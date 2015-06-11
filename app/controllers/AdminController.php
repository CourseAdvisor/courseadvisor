<?php
use JpGraph\JpGraph;

if(!function_exists('imageantialias'))
{
  // Quick and dirty fix for JpGraph... TODO: refactor
  function imageantialias($a, $b) {
    return true;
  }
}

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

    JpGraph::load();


    return View::make('admin.index')->with([
      'nbWaiting' => $nbWaiting,
      'nbRejected' => $nbRejected,
      'nbAccepted' => $nbAccepted,
      'stats' => $stats,
      'nbCourses' => Course::count(),
      'reviewsGraph' => $this->generateReviewsGrowthGraph(),
      'studentsGraph' => $this->generateRepartitionSectionGraph(),
      'coursesGraph' => $this->generateCourseCoverageGraph()
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

    return View::make('admin.listStudents')->with([
      'students' => $students
    ]);
  }

  public function listReviews() {
    $student = null;

    if (Input::has('sciper')) {
      $reviews = Review::whereHas('student', function($q) {
        return $q->where('sciper', Input::get('sciper'));
      })->orderBy('id', 'DESC')->get();
      $student = Student::where('sciper', Input::get('sciper'))->first();
      if (!$student) {
        throw new ModelNotFoundException;
      }

      $crumb = 'See reviews of ' . $student->fullname;
    }
    else {
      $reviews = Review::orderBy('id', 'DESC')->get();
      $crumb = 'See all reviews';
    }

    $this->addCrumb('AdminController@listReviews', $crumb);

    return View::make('admin.listReviews')->with([
      'reviews' => $reviews,
      'particularStudent' => Input::has('sciper'),
      'student' => $student
    ]);
  }

  private function generateCourseCoverageGraph() {
    $total = Course::count();
    $covered = Db::table('courses')
      ->select(DB::raw('COUNT(*) AS covered'))
      ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('reviews')
              ->whereRaw('reviews.course_id = courses.id');
      })->first()->covered;
    $coveredWithText = Db::table('courses')
      ->select(DB::raw('COUNT(*) AS covered'))
      ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('reviews')
              ->whereRaw('reviews.course_id = courses.id')
              ->where('reviews.comment', '!=', '');
      })->first()->covered;

    JpGraph::module('pie');
    $graph = new PieGraph(300, 300);
    $graph->title->Set("Courses");

    $plot = new PiePlot([$coveredWithText, $covered - $coveredWithText, $total - $covered]);
    $plot->SetLegends(['commented', 'rated', 'unrated']);
    $graph->Add($plot);
    $plot->SetSliceColors(['#c61c1c', '#b57070', '#655050']);
    $image = $graph->Stroke(_IMG_HANDLER);
    ob_start();
      imagepng($image);
      $graphData = ob_get_contents();
    ob_end_clean();

    return base64_encode($graphData);
  }

  private function generateRepartitionSectionGraph() {
    $students = Student::get();
    $bySection = $students->groupBy(function($student) {
      return $student->section->string_id;
    })->toArray();
    $sectionStats = array_map(function($entry) {
      return sizeof($entry);
    }, $bySection);

    array_multisort($sectionStats, SORT_DESC);

    JpGraph::module('bar');
    $graph = new Graph(800, 300);
    $graph->SetScale("textlin");
    $graph->title->Set("Sections");
    $graph->xaxis->SetTickLabels(array_keys($sectionStats));

    $plot = new BarPlot(array_values($sectionStats));
    $graph->Add($plot);
    $plot->SetColor("white");
    $plot->SetFillColor('#c61c1c');
    $image = $graph->Stroke(_IMG_HANDLER);
    ob_start();
      imagepng($image);
      $graphData = ob_get_contents();
    ob_end_clean();

    return base64_encode($graphData);
  }

  private function generateReviewsGrowthGraph() {

    $byDate = DB::table('reviews')
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
        ->groupBy(DB::raw('DATE(created_at)'))
        ->get();

    // plot data
    $data = array();
    $keys = array();

    $i = 0; // index in data
    $acc = 0; // accumulate totals

    // Iterate from Mon 2015/05/04 until now
    $date = new DateTime("2015-05-04");
    $now = new DateTime();
    $step = new DateInterval('P1D');
    while($date < $now) {
      // Only write mondays in legend
      $keys[] = ($date->format("D") == "Mon") ? $date->format("d/m") : "";
      // If we have some data for this day
      if (isset($byDate[$i]) && $byDate[$i]->date == $date->format("Y-m-d")) {
        // accumulate
        $data[] = ($acc += $byDate[$i]->total);
        $i++;
      } else {
        // otherwise, keep the same amount
        $data[] = $acc;
      }
      $date->add($step);
    }

    $graph = new Graph(800, 300);
    $graph->SetScale("textlin");
    $graph->xaxis->SetTickLabels($keys);

    JpGraph::module('line');
    $plot = new LinePlot($data);
    $graph->Add($plot);
    $plot->SetColor('#c61c1c');
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