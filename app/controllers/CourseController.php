<?php
class CourseController extends Controller {

	public function index() {
		$coursesPerPage = Config::get('app.nbCoursesPerPage');

		return View::make('courses.list', [
			'courses'	=> Course::with('sections')->paginate($coursesPerPage)
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