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
      $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
      $mp->track('Homepage landing', [
        'Locale' => LaravelLocalization::getCurrentLocale()
      ]);
      return View::make('homepage');
    }

  }

}
