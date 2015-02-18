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
		$count = Student::where('sciper', '=', $sciper)->count();

		if($count == 0) {
			$fullSection = explode(',', Tequila::get('unit'))[0]; // Of the form IN-BA6
			$splitted = explode('-', $fullSection);
			$sectionId = $splitted[0];
			$semester = $splitted[1];
			
			// Check that the section exists
			$section = Section::where('string_id', '=', $sectionId)->firstOrFail();

			Student::create(array(
				'firstname' => Tequila::get('firstname'),
				'lastname'	=> Tequila::get('name'),
				'email'		=> Tequila::get('email'),
				'sciper'	=> $sciper,
				'semester'	=> 'BA6', 
				'section_id'=> $section->id
			));

			Session::flash('message', array('type' => 'success', 'message' => 'Welcome on CourseAdvisor!'));

		}
		else {
			Session::flash('message', array('type' => 'success', 'message' => 'Welcome back, ' . Tequila::get('firstname') . '!'));
		}



		if(Session::has('login.next')) {
			return Redirect::to(Session::pull('login.next'));
		}

		return Redirect::to('/');
	}

	public function logout() {
		return Tequila::logout();
	}
}