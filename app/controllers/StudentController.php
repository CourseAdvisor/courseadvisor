<?php
class StudentController extends BaseController {

  /*public function index() {
    $students = Student::all();
    return View::make('student.list')->withStudents($students);
  }

  public function show($id) {
    $student = Student::with('courses')->find($id);

    if(!$student) {
      return App::abort(404);
    }

    return View::make('student.show')->withStudent($student);
  }*/

  public function dashboard() {
    $this->addCrumb('StudentController@dashboard', 'dashboard');
    return View::make('student.dashboard', [
      'page_title' => 'dashboard',
      'student' => Student::findOrFail(Session::get('student_id')),
      'plans' => StudyPlan::with('studyCycle')->get(),
    ]);
  }
}