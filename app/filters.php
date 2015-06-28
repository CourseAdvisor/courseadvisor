<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
  //
});


App::after(function($request, $response)
{
  //
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
  if (!Tequila::isLoggedIn())
  {
    if (Request::ajax())
    {
      $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
      $mp->track('Unauthorized action ', [
        'route' => Route::getCurrentRoute()->getPath(),
        'ab_group' => Session::get('ab_group')
      ]);
      return Response::make('Unauthorized', 401);
    }
    else
    {
      return Redirect::action('AuthController@login', ['next' => Request::url()]);
    }
  }
});

Route::filter('admin', function() {
  if (!StudentInfo::isAdmin()) {
    return Redirect::to(Config::get('content.rickroll_url'));
  }
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
  if (Tequila::isLoggedIn())
    return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
  if (Session::token() !== Input::get('_token'))
  {
    throw new Illuminate\Session\TokenMismatchException;
  }
});


Route::filter('locale', function() {
  setlocale(LC_ALL, trans('global.locale_code'));
});


/*
  Sets up analytics session
*/
Route::filter('mixpanel_identity', function() {
  $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
  if (!Session::has('mp_id')) {
    Session::put('mp_id', uniqid('mp', true));
  }
  $mp->identify(Session::get('mp_id'));

  if (Tequila::isLoggedIn()) {
    $mp->people->set(StudentInfo::getSciper(), [
      'name' => StudentInfo::getFullName(),
      'section' => StudentInfo::getFullSection(),
      'sciper' => StudentInfo::getSciper()
    ]);
  }
});

/*
  Ensures user belongs to an AB testing group.
*/
Route::filter('ab_testing', function() {
  if (!Session::has('ab_group')) {
    Session::put('ab_group', rand(0, 1) == 0 ? 'A' : 'B');
  }
});
