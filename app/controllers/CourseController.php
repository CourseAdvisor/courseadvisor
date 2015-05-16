<?php
class CourseController extends BaseController {

	public function __construct() {
		parent::__construct();
		$this->addCrumb('CourseController@studyCycles', 'Courses');
	}

    public function studyPlanCourses($cycle_name, $slug) {
        $coursesPerPage = Config::get('app.nbCoursesPerPage');

        $plan = StudyPlan::with('studyCycle')
            ->whereHas('studyCycle', function($q) use($cycle_name)
            {
                $q->where('name_en', $cycle_name)->orWhere('name_fr', $cycle_name);
            })
            ->where('slug', $slug)
            ->firstOrFail();

        $this->addCrumb('CourseController@studyPlans', ucfirst($cycle_name), ['cycle' => $cycle_name]);
        $this->addCrumb('CourseController@studyPlanCourses', ucfirst($plan->name), [
            'cycle' => $cycle_name,
            'plan_slug' => $slug]);

        return View::make('courses.planCourses', [
            'page_title' => $cycle_name.' &ndash; '.$plan->name,
            'plan' => $plan,
            'cycle' => $cycle_name,
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

        $this->addCrumb('CourseController@studyPlans', ucfirst($cycle_name), ['cycle' => $cycle_name]);

		return View::make('courses.plans', [
			'page_title' => $cycle_name.' courses',
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
		$course = Course::with('teacher', 'plans')->findOrFail($id);

		if (($realSlug = Str::slug($course->name)) != $slug) {
			return Redirect::action('CourseController@show', ['slug' => $realSlug, 'id' => $id]);
		}

		$this->addCrumb(Route::current()->getActionName(), $course->name, Route::current()->parameters());

		$hasAlreadyReviewed = false;
		if(Tequila::isLoggedIn()) {
			$hasAlreadyReviewed = $course->alreadyReviewedBy(Session::get('student_id'));
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

		$allReviews = $course->reviews()->with('student', 'student.section')->published();

		return View::make('courses.show', [
			'page_title' => $course->name,
			'course' => $course,
			'slug' 	=> $slug,
			'distribution' => $distribution,
			'reviews' => $allReviews->paginate($reviewsPerPage),
			'hasAlreadyReviewed' => $hasAlreadyReviewed,
			'nbReviews' => $allReviews->count(),
		]);
	}

	/**
	 *	Shows a teacher's courses and maybe some stats for that teacher
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
		$validator = Validator::make(Input::all(), Review::rules());
		$goToCourse = Redirect::action('CourseController@show', [$slug, $courseId]);
		if ($validator->fails()) {
			return Redirect::to(URL::previous() . "#my-review")
					->withInput()
					->withErrors($validator);
		}

		// Get course and student info
		$course = Course::findOrFail($courseId); // Fails if the course doesn't exist
		$studentId = Session::get('student_id');

		// Check if the course was not already reviewed by the student
		if($course->alreadyReviewedBy($studentId)) {
			return $goToCourse->with('message', ['danger', 'You can\'t review a course twice. Nice try!']);
		}

		// Create the review
		$data = ['course_id' => intval($courseId), 'student_id' => $studentId];

		$newReview = new Review(Input::all());
		$newReview->course_id = $courseId;
		$newReview->student_id = $studentId;
		$newReview->updateAverage();


		// Check if we should use 'mobile_difficulty'
		if(empty($newReview->difficulty)) {
			$newReview->difficulty = Input::get('difficulty_mobile');
		}
		// difficulty 0 means N/A
		if ($newReview->difficulty == 0)
			unset($newReview->difficulty);

		if(Input::get('anonymous') == true) {
			$newReview->is_anonymous = 1;
			$newReview->status = 'waiting';
		}

		$newReview->save();

		// Update averages only if the review is not anonymous
		if (!$newReview->is_anonymous) {
			$course->updateAverages();
			$msg = 'Your review was successfuly posted. Thank you!';
		}
		else {
			$msg = 'Thank you! Your review has been submitted for moderation. It will appear on this page anytime soon.';
		}


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
				->with('message', ['danger', 'You are not allowed to edit this review.']);
		}

		// Check input data
		$validator = Validator::make(Input::all(), Review::rules());
		if ($validator->fails()) {
			return Redirect::to(URL::previous() . "#!edit-" . $review->id)
					->withInput()
					->withErrors($validator);
		}

		$review->comment = Input::get('comment');
		$review->title = Input::get('title');
		$review->lectures_grade = Input::get('lectures_grade');
		$review->exercises_grade = Input::get('lectures_grade');
		$review->content_grade = Input::get('content_grade');
		$review->difficulty = Input::get('difficulty');

        $msg = 'Your review has been successfuly edited';

		if (Input::get('anonymous') == true) {
			$review->is_anonymous = 1;
            $review->status = 'waiting';
            $msg = 'Your review has been edited. It will now be reviewed by an administrator';
		}

		$review->updateAverage();
		$review->save();

		if (!$review->is_anonymous)
			$review->course->updateAverages();

		return $courseRedirect
				->with('message', ['success', $msg]);
	}
}
