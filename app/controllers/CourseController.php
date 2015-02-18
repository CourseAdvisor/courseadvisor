<?php
class CourseController extends Controller {

	public function index() {
		$courses = Course::with('sections')->get();

		return View::make('courses.list', [
			'courses'	=> $courses	
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
		$course = Course::with('students.section')->find($id);

		if(!$course) {
			return App::abort(404);
		}

		return View::make('courses.show', [
			'course' 		=> $course, 
			'studentCount'	=> count($course->students)
		]);
	}
}