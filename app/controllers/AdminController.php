<?php
use JpGraph\JpGraph;

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
		$this->addCrumb('AdminController@moderate', 'Moderate reviews');
		$reviews = Review::with('student', 'student.section')->waiting()->get();

		return View::make('admin.moderate')->with([
			'reviews' => $reviews
		]);
	}

	public function listStudents() {
		$this->addCrumb('AdminController@listStudents', 'View registred students');
		$students = Student::with('reviews')->orderBy('id', 'desc')->get();
		JpGraph::load();
		JpGraph::module('pie');
		JpGraph::module('bar');

		$repartitionSectionGraphData = $this->generateRepartitionSectionGraph($students);
		$repartitionNbReviewsGraphData = $this->generateRepartitionNbReviewsGraphData($students);
		return View::make('admin.listStudents')->with([
			'students' => $students,
			'repartitionSectionGraphData' => $repartitionSectionGraphData,
			'repartitionNbReviewsGraphData' => $repartitionNbReviewsGraphData
		]);
	}

	private function generateRepartitionSectionGraph($students) {
		$bySection = $students->groupBy(function($student) {
			return $student->section->name;
		})->toArray();
		$sectionStats = array_map(function($entry) {
			return sizeof($entry);
		}, $bySection);

		$graph = new PieGraph(300, 300);
		$graph->title->Set("Sections");

		$plot = new PiePlot(array_values($sectionStats));
		$plot->SetLegends(array_keys($sectionStats));
		$graph->Add($plot);
		$image = $graph->Stroke(_IMG_HANDLER);
		ob_start();
			imagepng($image);
			$graphData = ob_get_contents();
		ob_end_clean();

		return base64_encode($graphData);
	}

	private function generateRepartitionNbReviewsGraphData($students) {
		$byNbReviews = $students->groupBy(function($student) {
			return $student->reviews()->count();
		})->toArray();

		$nbReviewsStats = array_map(function($entry) {
			return sizeof($entry);
		}, $byNbReviews);

		$stats = [];
		$max = max(array_keys($nbReviewsStats));
		for($i = 0; $i <= $max; ++$i) {
			if (isset($nbReviewsStats[$i])) {
				$stats[] = $nbReviewsStats[$i];
			}
			else {
				$stats[] = 0;
			}
		}

		$graph = new Graph(500, 300);
		$graph->SetScale('intlin');
		$graph->img->SetAntiAliasing(false);
		$graph->title->Set("Number of reviews posted");
		$graph->xaxis->title->Set("Number of reviews posted");
		$graph->yaxis->title->Set("Number of students");

		$plot = new BarPlot(array_values($stats));

		$graph->SetMargin(40,30,50,40);
		$graph->Add($plot);
		$graph->yaxis->scale->SetGrace(30);

		$image = $graph->Stroke(_IMG_HANDLER);
		ob_start();
			imagepng($image);
			$graphData = ob_get_contents();
		ob_end_clean();

		return base64_encode($graphData);

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