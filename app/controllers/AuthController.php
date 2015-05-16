<?php
class AuthController extends Controller {
	public function login() {
		return Tequila::login(Input::get('next', '/'));
	}

	// called after the user logged in on tequila
	public function loginRedirect() {
		$result = Tequila::loginRedirect();
		if($result !== true) {
			return $result; // 403 or redirect for error
		}

		// Login was successful

		// First, check if the user is already in the database
		$sciper = Tequila::get('uniqueid');
		$student = Student::where('sciper', '=', $sciper);

        $fullSection = explode(',', Tequila::get('unit'))[0]; // Of the form IN-BA6
        $splitted = explode('-', $fullSection);
        $sectionId = $splitted[0];

        $count = $student->count();
        if($count == 0) {
            // It's a new user

			// Check that the section exists
			$section = Section::where('string_id', '=', $sectionId)->firstOrFail();

			$student = Student::create(array(
				'firstname' => Tequila::get('firstname'),
				'lastname'	=> Tequila::get('name'),
				'email'		=> Tequila::get('email'),
				'sciper'	=> $sciper,
				'semester'	=> StudentInfo::getSemester(),
				'section_id'=> $section->id
			));

			Session::put('student_id', $student->id);
			Session::flash('message', ['success', trans('global.welcome-message')]);

		}
		else {
			// The student is already in the database, update him if needed
			$student = $student->first();
			$currentSemester = StudentInfo::getSemester();
			$section = Section::where('string_id', '=', StudentInfo::getSection())->firstOrFail();
			if($student->section_id != $section->id || $student->semester != $currentSemester) {
				$student->semester = $currentSemester;
				$student->section_id = $section->id;
				$student->save();
			}
			Session::put('student_id', $student->id);
			if ($student->is_admin) {
				Session::put('is_admin', 1);
			}
			Session::flash('message', [
				'success',
				trans('global.welcome-back-message', ['name' => Tequila::get('firstname')])
			]);
		}

        $student->refreshPlans($sectionId);

		if(Session::has('login.next')) {
			return Redirect::to(Session::pull('login.next'));
		}

		return Redirect::to('/');
	}

	public function logout() {
		return Tequila::logout();
	}
}
