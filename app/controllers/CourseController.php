<?php
class CourseController extends BaseController {

	public function __construct() {
		parent::__construct();
		$this->addCrumb('CourseController@sections', 'Courses');
	}

	public function listBySectionSemester($section_id, $semester) {

		$coursesPerPage = Config::get('app.nbCoursesPerPage');
		$section_name = null;

		$courses = Course::with('sections', 'teacher')->whereHas('sections', function($q) use ($section_id, $semester) {
			if (!is_null($section_id))
				$q->where('string_id', '=', $section_id);
			if (!is_null($semester) && $semester != 'ALL')
				$q->where('semester', '=', $semester);
		})->paginate($coursesPerPage);

		$section_name = Section::where('string_id', '=', $section_id)->firstOrFail()->name;
		$this->addCrumb('CourseController@sectionSemester', ucfirst($section_name), [
			'section_id' => $section_id
		]);

		if ($semester == 'ALL') {
			$this->addCrumb(Route::current()->getActionName(), 'All semesters', Route::current()->parameters());
		} else {
			$this->addCrumb(Route::current()->getActionName(), $semester, Route::current()->parameters());
		}

		return View::make('courses.list', [
			'page_title' => $section_name.' &ndash; '.$semester,
			'courses' => $courses,
			'section' => $section_name
		]);
	}

	public function sections() {
		return View::make('courses.sections', [
			'page_title' => 'Sections',
			'sections' => Section::get()
		]);
	}

	public function sectionSemester($section_id) {
		$section = Section::where('string_id', '=', $section_id)->firstOrFail();

		$this->addCrumb('CourseController@sectionSemester', ucfirst($section->name), [
			'section_id' => $section->name
		]);

		return View::make('courses.sectionSemester', [
			'page_title' => $section->name,
			'section' => $section,
			'semesters' => DB::table('course_section')->select('semester')->distinct()->orderBy('semester')->get()
		]);
	}

	public function suggestions() {
		$courses = Course::whereHas('sections', function($q) {
			$q->where('string_id', '=', StudentInfo::getSection());
			$q->whereIn('semester', StudentInfo::getLowerSemesters());
		})->get();

		return View::make('courses.suggestions', [
			'courses'	=> $courses
		]);
	}

	public function show($slug, $id) {
		$course = Course::with('teacher', 'sections')->findOrFail($id);

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
			$msg = 'Your review was successfuly posted. It will now be moderated by an administrator.';
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

		if (Input::get('anonymous') == true) {
			$review->is_anonymous = 1;
		}

		$review->updateAverage();
		$review->save();

		if (!$review->is_anonymous)
			$review->course->updateAverages();

		return $courseRedirect
				->with('message', ['success', 'Your review has been successfuly edited']);
	}
}
