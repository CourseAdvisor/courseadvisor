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

    return View::make('courses.planCourses', [
      'page_title' => $cycle.' &ndash; '.$plan->name,
      'plan' => $plan,
      'cycle' => $cycle,
      'courses' => $plan->courses()
        ->with('teacher', 'plans')
        ->withPivot('semester')
        ->orderBy('pivot_semester')
        ->get()
        ->groupBy(function($course) {
          return $course->nice_semester;
        })
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
      'teacher',
      'plans',
      'plans.studyCycle',
      'reviews' => function($q) {
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

    $coursesPerPage = Config::get('app.nbCoursesPerPage');
    $teacher = Teacher::findOrFail($id);

    return View::make('courses.teacher', [
      'page_title' => $teacher->fullname,
      'slug' => $slug,
      'teacher' => $teacher,
      'courses' => $teacher->courses()->paginate($coursesPerPage)
    ]);
  }

  public function createReview($slug, $courseId) {
    $validator = Review::getValidator(Input::all());
    if ($validator->fails()) {
      return Redirect::to(URL::previous() . "#my-review")
          ->withInput()
          ->withErrors($validator);
    }

    // Get course and student info
    $course = Course::findOrFail($courseId); // Fails if the course doesn't exist
    $studentId = Session::get('student_id');
    $goToCourse = Redirect::action('CourseController@show', [$slug, $courseId]);

    // Check if the course was not already reviewed by the student
    if($course->alreadyReviewedBy($studentId)) {
      return $goToCourse->with('message', ['danger', trans('courses.review-create-not-allowed')]);
    }

    // Create the review
    $data = ['course_id' => intval($courseId), 'student_id' => $studentId];

    $newReview = new Review(Input::all());
    $newReview->course_id = $courseId;
    $newReview->student_id = $studentId;
    $newReview->updateAverage();


    // Check if we should use 'mobile_difficulty'
    if (Input::has('difficulty_mobile') && Input::get('difficulty_mobile') != 0) {
      $newReview->difficulty = Input::get('difficulty_mobile');
    }

    if(Input::get('anonymous') == true) {
      $newReview->is_anonymous = 1;
      $newReview->status = 'waiting';
    }

    $newReview->save();
    Event::fire('review.newReview', [$newReview]);

    // Update averages only if the review is not anonymous
    if (!$newReview->is_anonymous) {
      $course->updateAverages();
      $msg = trans('courses.review-posted-message');
    }
    else {
      $msg = trans('courses.review-posted-anonymous-message');
    }

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Posted a review', [
        'Course name' => $course->name,
        'Average grade' => $newReview->avg_grade,
        'Exercises grade' => $newReview->exercises_grade,
        'Lectures grade' => $newReview->lectures_grade,
        'Content grade' => $newReview->content_grade,
        'Difficulty' => $newReview->difficulty,
        'Is review' => $newReview->isReview(),
        'Anonymous' => $newReview->is_anonymous == 1,
        'Locale' => LaravelLocalization::getCurrentLocale()
    ]);

    return $goToCourse->with('message', ['success', $msg]);
  }

  public function updateReview($slug, $courseId) {
    if (!Input::has('reviewId')) {
      return Redirect::to('/');
    }

    $courseRedirect = Redirect::action('CourseController@show', [$slug, $courseId]);

    // Retrieve review
    $review = Review::findOrFail(Input::get('reviewId'));

    // Check authorized
    if ($review->student_id != StudentInfo::getId()) {
      return $courseRedirect
        ->with('message', ['danger', trans('courses.review-update-not-allowed')]);
    }

    // Check input data
    $validator = Review::getValidator(Input::all());
    if ($validator->fails()) {
      return Redirect::to(action('CourseController@show', [$slug, $courseId]) . "#!xedit-" . $review->id)
          ->withInput()
          ->withErrors($validator);
    }

    $review->comment = Input::get('comment');
    $review->title = Input::get('title');
    $review->lectures_grade = Input::get('lectures_grade');
    $review->exercises_grade = Input::get('exercises_grade');
    $review->content_grade = Input::get('content_grade');
    $review->difficulty = Input::get('difficulty');

    $msg = trans('courses.review-updated-message');

    if (Input::get('anonymous') == true) {
        $review->is_anonymous = 1;
        $review->status = 'waiting';
        $msg = trans('courses.review-updated-anonymous-message');
    } else {
      $review->is_anonymous = 0;
    }

    $review->updateAverage();
    $review->save();

    if ($review->is_anonymous) {
      Event::fire('review.newAnonymous', [$review]);
    }

    $review->course->updateAverages();

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Updated a review', [
      'Course name' => $review->course->name,
      'Average grade' => $review->avg_grade,
      'Exercises grade' => $review->exercises_grade,
      'Lectures grade' => $review->lectures_grade,
      'Content grade' => $review->content_grade,
      'Difficulty' => $review->difficulty,
      'Is review' => $review->isReview(),
      'Anonymous' => $review->is_anonymous == 1,
      'Locale' => LaravelLocalization::getCurrentLocale()
    ]);

    return $courseRedirect
        ->with('message', ['success', $msg]);
  }

  public function deleteReview($slug, $courseId, $reviewId) {
    $review = Review::findOrFail($reviewId);
    $courseRedirect = Redirect::action('CourseController@show', [$slug, $courseId]);

    // Check authorized
    if ($review->student_id != StudentInfo::getId()) {
      return $courseRedirect
        ->with('message', ['danger', trans('courses.review-delete-not-allowed')]);
    }

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Deleted a review', [
        'Course name' => $review->course->name,
        'Comments' => count($review->comments),
        'Average grade' => $review->avg_grade,
        'Exercises grade' => $review->exercises_grade,
        'Lectures grade' => $review->lectures_grade,
        'Content grade' => $review->content_grade,
        'Difficulty' => $review->difficulty,
        'Is review' => $review->isReview(),
        'Anonymous' => $review->is_anonymous == 1,
        'Locale' => LaravelLocalization::getCurrentLocale()
    ]);

    $review->delete();
    $review->course->updateAverages();

    return $courseRedirect
      ->with('message', ['success', trans('courses.review-deleted-message')]);
  }
}
