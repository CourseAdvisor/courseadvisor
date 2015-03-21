<?php
class CourseController extends BaseController {

	public function __construct() {
		parent::__construct();
		$this->addCrumb('CourseController@sections', 'Courses');
	}

	public function listBySectionSemester($section_id = null, $semester = null) {

		$coursesPerPage = Config::get('app.nbCoursesPerPage');
		$section_name = null;

		$courses = Course::whereHas('sections', function($q) use ($section_id, $semester) {
			if (!is_null($section_id))
				$q->where('string_id', '=', $section_id);
			if (!is_null($semester) && $semester != 'ALL')
				$q->where('semester', '=', $semester);
		})->paginate($coursesPerPage);

		if (!is_null($section_id)) {
			$section_name = Section::where('string_id', '=', $section_id)->firstOrFail()->name;
			$this->addCrumb('CourseController@sectionSemester', ucfirst($section_name), [
				'section_id' => $section_id
			]);

			if (!is_null($semester)) {
				if ($semester == 'ALL') {
					$this->addCrumb(Route::current()->getActionName(), 'All semesters', Route::current()->parameters());
				} else {
					$this->addCrumb(Route::current()->getActionName(), $semester, Route::current()->parameters());
				}
			}
		}

		return View::make('courses.list', [
			'courses' => $courses,
			'section' => $section_name
		]);
	}

	public function sections() {
		return View::make('courses.sections', [
			'sections' => Section::get()
		]);
	}

	public function sectionSemester($section_id) {
		$section = Section::where('string_id', '=', $section_id)->firstOrFail();

		$this->addCrumb('CourseController@sectionSemester', ucfirst($section->name), [
			'section_id' => $section->name
		]);

		return View::make('courses.sectionSemester', [
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
		$course = Course::with('teacher')->findOrFail($id);

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

		return View::make('courses.show', [
			'course' => $course,
			'slug' 	=> $slug,
			'distribution' => $distribution,
			'reviews' =>$course->reviews()->with('student')->paginate($reviewsPerPage),
			'hasAlreadyReviewed' => $hasAlreadyReviewed,
			'nbReviews' => $course->reviews->count()
		]);
	}

	/**
	 *	Shows a teacher's courses and maybe some stats for that teacher
	 */
	public function showTeacher($slug, $id) {
		$teacher = Teacher::with('courses')->findOrFail($id);

		return View::make('courses.teacher', [
			'slug' => $slug,
			'teacher' => $teacher,
			'courses' => $teacher->courses
		]);
	}

	public function createReview($slug, $courseId) {
		$validator = Validator::make(Input::all(), Review::rules());
		$goToCourse = Redirect::action('CourseController@show', [$slug, $courseId]);
		if ($validator->fails()) {
			return $goToCourse
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

		if(Input::get('anonymous') == true) {
			$newReview->is_anonymous = 1;
		}

		$newReview->save();
		$course->updateAverages();

		return $goToCourse->with('message', ['success', 'Your review was successfuly posted. Thank you!']);
	}
}
