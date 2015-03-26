<?php
class StaticController extends BaseController {
	public function faq() {
		return "coucou";
	}

    public function homepage()
    {
        if (Tequila::isLoggedIn()) {
            // TODO: redirect to dashboard
            return Redirect::action('CourseController@sections');
        } else {
            return View::make('homepage');
        }
    }

}
