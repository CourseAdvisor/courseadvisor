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


Route::pattern('id', '\d+');
Route::pattern('slug', '[a-zA-Z0-9_\-\.]+');

Route::get('/', 'CourseController@suggestions');
Route::get('/faq', 'StaticController@faq');
Route::get('/students', 'StudentController@index');
Route::get('/students/{id}', 'StudentController@show');
Route::get('/courses', 'CourseController@index');
Route::get('/courses/{slug}-{id}', 'CourseController@show');

Route::get('/login', array('before' => 'logged_out', 'uses' => 'AuthController@login'));
Route::get('/login_redirect', 'AuthController@loginRedirect');

/* Routes requiring login (display error message if not logged in) */
Route::group(array('before' => 'logged_in'), function() {
	Route::get('/logout', 'AuthController@logout');
});

/* Routes forcing login */
Route::group(array('before' => 'force_login'), function() {
	Route::post('/courses/{slug}-{id}/createReview', 'CourseController@createReview');
});

Route::when('*', 'csrf', array('post', 'put', 'delete'));

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