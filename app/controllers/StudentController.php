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

    $student = Student::findOrFail(Session::get('student_id'));

    $studentCourses = $student->inscriptions()
                      ->orderBy('year', 'DESC')
                      ->get();
    $studentCourses = $studentCourses->map(function($inscription) {
      return $inscription->course;
    });

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Dashboard shown', [
        'Locale' => LaravelLocalization::getCurrentLocale()
    ]);

    $studentPlans = $student->studyPlans;
    if (count($studentPlans) > 0) {
      $studyPlanId = $studentPlans[0]->id;
    } else {
      $studyPlanId = 0;
    }
    return View::make('student.dashboard', [
      'page_title' => 'dashboard',
      'student' => $student,
      'studyPlanId' => $studyPlanId,
      'studentCourses' => $studentCourses,
      'plans' => StudyPlan::with('studyCycle', 'studyCycle.plans.courses')->get(),
    ]);
  }
}
