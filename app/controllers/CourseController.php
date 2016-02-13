<?php
class CourseController extends BaseController {

  public function __construct() {
    parent::__construct();
    $this->addCrumb('CourseController@studyCycles', 'Courses');
  }

  public function findStudyPlan() {
    $plan = StudyPlan::find(Input::get('plan-id'));

    if(is_null($plan)) {
      return Response::view('errors.missing', array('url' => Request::url()), 404);
    }

    return Redirect::action('CourseController@studyPlanCourses', ['plan_slug' => $plan->slug, 'cycle' => $plan->studyCycle->name]);
  }

  public function studyPlanCourses($cycle, $plan_slug) {
    $coursesPerPage = Config::get('app.nbCoursesPerPage');

    $plan = StudyPlan::with('studyCycle')
      ->whereHas('studyCycle', function($q) use($cycle) {
        $q->where('name_en', $cycle)->orWhere('name_fr', $cycle);
      })
      ->where('slug', $plan_slug)
      ->firstOrFail();

    $this->addCrumb('CourseController@studyPlans', ucfirst($plan->studyCycle->name), ['cycle' => $plan->studyCycle->name]);
    $this->addCrumb('CourseController@studyPlanCourses', ucfirst($plan->name), [
      'cycle' => $cycle,
      'plan_slug' => $plan_slug
    ]);

    $courses = $plan->courses()
      ->orderBy('pivot_semester')
      ->get()
      ->groupBy(function($course) {
        return $course->nice_semester;
      });

    foreach($courses as $semester => $_courses) {
      usort($_courses, function ($c1, $c2) {
        return strcmp($c1->name, $c2->name);
      });
      $courses[$semester] = $_courses;
    }

    return View::make('courses.planCourses', [
      'page_title' => $cycle.' &ndash; '.$plan->name,
      'plan' => $plan,
      'cycle' => $cycle,
      'courses' => $courses
    ]);
  }

  public function studyPlans($cycle_name) {
    $cycle = StudyCycle::where('name_fr', $cycle_name)->orWhere('name_en', $cycle_name)->firstOrFail();

    $this->addCrumb('CourseController@studyPlans', ucfirst( $cycle->name), ['cycle' =>  $cycle->name]);

    return View::make('courses.plans', [
      'page_title' =>  $cycle->name.' courses',
      'plans' => $cycle->plans,
      'cycle' => $cycle->name
    ]);
  }

  public function studyCycles() {
    return View::make('courses.cycles', [
      'page_title' => 'Study cycles',
      'cycles' => StudyCycle::get()
    ]);
  }

  public function show($slug, $id) {
    $course = Course::with([
      'instances',
      'plans',
      'plans.studyCycle',
      'instances.reviews' => function($q) {
        $q->published();
      }
    ])->findOrFail($id);

    if (($realSlug = $course->slug) != $slug) {
      return Redirect::action('CourseController@show', ['slug' => $realSlug, 'id' => $id]);
    }

    $this->addCrumb(Route::current()->getActionName(), $course->name, Route::current()->parameters());

    $hasAlreadyReviewed = false;
    $studentReview = null;
    if(Tequila::isLoggedIn()) {
      $studentReview = $course->alreadyReviewedBy(Session::get('student_id'));
      $hasAlreadyReviewed = $studentReview != null;
    }

    $reviewsPerPage = Config::get('app.nbReviewsPerPage');

    // Build distribution
    $distribution = [];
    foreach(range(0, 4) as $i) {
      $nbFilteredReviews = $course->reviews->filter(function($review) use ($i, $course) {
        return round($review->avg_grade) == 1 + $i;
      })->count();
      $distribution[$i] = [
        'percentage' => ($course->reviews->count() > 0) ? 100 * $nbFilteredReviews / $course->reviews->count() : 0,
        'total' => $nbFilteredReviews
      ];
    }

    $allReviews = $course->reviews()
      ->orderBy('score', 'desc')->orderBy('created_at', 'desc')
      ->with('comments', 'student', 'student.section')->published();

    // Warning: keep statements in this order as laravel query builder is not immutable (shame on them!)
    $nbVotes = $allReviews->count();
    $allReviews->where('title', '!=', '');
    $nbReviews = $allReviews->count();
    $reviews = $allReviews->paginate($reviewsPerPage);

        $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
        $mp->track('Viewed a course', [
            'Course name' => $course->name,
            'Nb reviews' => $nbReviews,
            'Grade' => $course->avg_overall_grade,
            'Has reviewed' => $hasAlreadyReviewed,
            'Locale' => LaravelLocalization::getCurrentLocale()
        ]);

    return View::make('courses.show', [
      'page_title' => $course->name,
      'course' => $course,
      'slug'   => $slug,
      'distribution' => $distribution,
      'reviews' => $reviews,
      'hasAlreadyReviewed' => $hasAlreadyReviewed,
      'studentReview' => $studentReview,
      'nbReviews' => $nbReviews,
      'nbVotes' => $nbVotes
    ]);
  }

  /**
   *  Shows a teacher's courses and maybe some stats for that teacher
     *
     * TODO: move in separate controller
   */
  public function showTeacher($slug, $id) {

    $teacher = Teacher::findOrFail($id);

    return View::make('courses.teacher', [
      'page_title' => $teacher->fullname,
      'slug' => $slug,
      'teacher' => $teacher,
      'courses' => $teacher->courses
    ]);
  }
}
