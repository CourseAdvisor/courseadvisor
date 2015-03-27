<?php
class AdminController extends BaseController {

	public function __construct() {
		parent::__construct();
		$this->addCrumb('AdminController@index', 'Administration');
	}

	public function index() {
		$nbWaiting = Review::waiting()->count();
		$nbRejected = Review::rejected()->count();
		$nbAccepted = Review::accepted()->count();

		$stats = [
			'nb_courses'  => DB::table('courses')->count(),
			'nb_reviews'  => DB::table('reviews')->count(),
			'nb_students' => DB::table('students')->count()
		];

		return View::make('admin.index')->with([
			'nbWaiting' => $nbWaiting,
			'nbRejected' => $nbRejected,
			'nbAccepted' => $nbAccepted,
			'stats' => $stats
		]);
	}

	public function moderate() {
		$reviews = Review::with('student', 'student.section')->waiting()->get();

		return View::make('admin.moderate')->with([
			'reviews' => $reviews
		]);
	}

	/*
	 * Should be called using ajax
	 */
	public function doModerate($reviewId, $decision) {
		$matchings = [
			'accept' => 'accepted',
			'reject' => 'rejected'
		];
		if (!array_key_exists($decision, $matchings) || !Request::ajax()) {
			return Redirect::to(URL::previous());
		}

		$review = Review::findOrFail($reviewId);
		$review->status = $matchings[$decision];
		$review->save();

		if ($decision == 'accept') {
			$review->course->updateAverages();
		}

		return ['result' => 'ok'];
	}
}