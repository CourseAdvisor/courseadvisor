<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/


/* PATTERNS */
Route::pattern('id', '\d+');
Route::pattern('slug', '[a-zA-Z0-9_\-\.]+');


// Localization wrapper
Route::group([
	'prefix' => LaravelLocalization::setLocale(),
	'before' => 'LaravelLocalizationRedirectFilter' // LaravelLocalization filter
], function() {
  /* All localized routes */
  Route::get('/', function()
  {
    return View::make('hello');
  });

	Route::get('/', 'StaticController@homepage');
	Route::get('/faq', 'StaticController@faq');

	Route::get('/search', 'SearchController@search');

	/*Route::get('/students', 'StudentController@index');
	Route::get('/students/{id}', 'StudentController@show');*/

	Route::get('/courses', 'CourseController@studyCycles');
	Route::get('/courses/{cycle}', 'CourseController@studyPlans');
	Route::get('/courses/{cycle}/{plan_slug}', 'CourseController@studyPlanCourses');
	// Route::get('/courses/{section_id}', 'CourseController@sectionSemester');
	// Route::get('/courses', 'CourseController@list');
	Route::get('/courses/{section_id}/{semester}', 'CourseController@listBySectionSemester');
	Route::get('/course/{slug}-{id}', 'CourseController@show');
	Route::get('/teacher/{slug}-{id}', 'CourseController@showTeacher');

	Route::get('/login', array('before' => 'logged_out', 'uses' => 'AuthController@login'));
	Route::get('/login_redirect', 'AuthController@loginRedirect');

	/* Routes requiring login (display error message if not logged in) */
	Route::group(array('before' => 'logged_in'), function() {
		Route::get('/logout', 'AuthController@logout');
	});

	/* Routes forcing login */
	Route::group(array('before' => 'force_login'), function() {
		Route::get('/dashboard', 'StudentController@dashboard');
		Route::post('/course/{slug}-{id}/createReview', 'CourseController@createReview');
		Route::post('/course/{slug}-{id}/updateReview', 'CourseController@updateReview');
	});

	/* Admin stuff */
	Route::group(['before' => 'admin_check'], function() {
		Route::get('/admin', 'AdminController@index');
		Route::get('/admin/moderate', 'AdminController@moderate');
		Route::get('/admin/moderate/{id}/{decision}', 'AdminController@doModerate');
		Route::get('/admin/students', 'AdminController@listStudents');
	});
});


Route::when('*', 'csrf', array('post', 'put', 'delete'));

Route::filter('admin_check', function() {
	if (!StudentInfo::isAdmin()) {
		return Redirect::to(Config::get('content.rickroll_url'));
	}
});

Route::filter('logged_in', function() {
	if(!Tequila::isLoggedIn())  {
		Session::flash('message', array('type' => 'danger', 'message' => 'You must be logged in to do this.'));
		return Redirect::to('/');
	}
});

Route::filter('force_login', function() {
	if(!Tequila::isLoggedIn())  {
		return Redirect::action('AuthController@login', ['next' => Request::url()]);
	}
});

Route::filter('logged_out', function() {
	if(Tequila::isLoggedIn())  {
		Session::flash('message', array('type' => 'danger', 'message' => 'You must be logged out to do this.'));
		return Redirect::to('/');
	}
});