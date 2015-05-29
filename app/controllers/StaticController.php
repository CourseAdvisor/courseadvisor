<?php
class StaticController extends BaseController {
	public function about() {
        $this->addCrumb('StaticController@about', trans('about.breadcrumb'));
		return View::make('about');
	}

    public function homepage()
    {
        if (Tequila::isLoggedIn()) {
            return Redirect::action('StudentController@dashboard');
        } else {
            return View::make('homepage');
        }
    }

}
