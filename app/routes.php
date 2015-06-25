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
Route::pattern('courseId', '\d+');
Route::pattern('reviewId', '\d+');
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
	Route::get('/about', 'StaticController@about');

	Route::get('/search', 'SearchController@search');

	/*Route::get('/students', 'StudentController@index');
	Route::get('/students/{id}', 'StudentController@show');*/

	Route::get('/courses', 'CourseController@studyCycles');
	Route::post('/courses/studyplan', 'CourseController@findStudyPlan');
	Route::get('/courses/{cycle}', 'CourseController@studyPlans');
	Route::get('/courses/{cycle}/{plan_slug}', 'CourseController@studyPlanCourses');
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
		Route::get('/course/{slug}-{courseId}/deleteReview/{reviewId}', 'CourseController@deleteReview');

	});

});


// === actions API ===
// Routes for posting / editing / deleting stuff.
// Looks like they don't need to be localized.

Route::group([
	'prefix' => 'api'
], function() {

	// --- Regular API ---

	// comments
	Route::post('/comment', 'ReviewController@createComment');
	Route::post('/comment/edit', 'ReviewController@updateComment');
	Route::post('/comment/delete', 'ReviewController@deleteComment');


	// --- AJAX API ---

	Route::group(array('before' => 'auth'), function() {
		// votes
		Route::post('/vote', 'ReviewController@vote');
	});
});


// === Admin ===
Route::group(['before' => 'admin_check'], function() {
	Route::get('/admin', 'AdminController@index');
	Route::get('/admin/moderate', 'AdminController@moderate');
	Route::post('/admin/moderate', 'AdminController@doModerate');
	Route::get('/admin/students', 'AdminController@listStudents');
	Route::get('/admin/reviews', 'AdminController@listReviews');
});


Route::when('*', 'csrf', array('post', 'put', 'delete'));
Route::when('*', 'mixpanel_identity', array('post', 'put', 'delete', 'get'));
Route::when('*', 'ab_testing', array('post', 'put', 'delete', 'get'));

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