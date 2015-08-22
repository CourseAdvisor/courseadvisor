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

  Route::get('/login', array('before' => 'guest', 'uses' => 'AuthController@login'));
  Route::get('/login_redirect', 'AuthController@loginRedirect');

  Route::get('/logout', 'AuthController@logout');

  /* Routes forcing login */
  Route::group(array('before' => 'auth'), function() {
    Route::get('/dashboard', 'StudentController@dashboard');
  });
});


// === actions API ===
// Routes for posting / editing / deleting stuff.
Route::group([
  'prefix' => 'api'
], function() {

  // Public

  // nothing so far


  // Restricted
  Route::group(['before' => 'auth'], function() {

    // --- Regular API ---

    // reviews
    Rotue::post('/review', 'ReviewController@createReview');
    Route::post('/review/edit', 'ReviewController@updateReview');
    Route::post('/review/delete', 'ReviewController@deleteReview');

    // comments
    Route::post('/comment', 'ReviewController@createComment');
    Route::post('/comment/edit', 'ReviewController@updateComment');
    Route::post('/comment/delete', 'ReviewController@deleteComment');


    // --- AJAX API ---

    // Basic route to ping for auth status
    Route::get('/is_auth', function() { return 'ok'; });
    // votes
    Route::post('/vote', 'ReviewController@vote');
  });
});


// === Admin stuff ===
Route::group(['before' => 'admin'], function() {
  Route::get('/admin', 'AdminController@index');
  Route::get('/admin/moderate', 'AdminController@moderate');
  Route::post('/admin/moderate', 'AdminController@doModerate');
  Route::get('/admin/students', 'AdminController@listStudents');
  Route::get('/admin/reviews', 'AdminController@listReviews');
});

Route::when('*', 'csrf', array('post', 'put', 'delete'));
Route::when('*', 'locale', array('post', 'put', 'delete', 'get'));
Route::when('*', 'mixpanel_identity', array('post', 'put', 'delete', 'get'));
Route::when('*', 'ab_testing', array('post', 'put', 'delete', 'get'));
