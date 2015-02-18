<?php
class StudentController extends BaseController {

	public function index() {
		$students = Student::all();
		return View::make('students.list')->withStudents($students);
	}

	public function show($id) {
		$student = Student::with('courses')->find($id);

		if(!$student) {
			return App::abort(404);
		}

		return View::make('students.show')->withStudent($student);
	}
}