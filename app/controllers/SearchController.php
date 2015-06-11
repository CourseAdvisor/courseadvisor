<?php

class SearchController extends BaseController {

  public function __construct() {
    parent::__construct();
  }

  public function search() {
    if (!Input::has('q')) {
      return Redirect::to('/');
    }
    $term = Input::get('q');
    $nbPerPage = Config::get('app.searchNbCoursesPerPage');

    $this->addCrumb(Route::current()->getActionName(), "Search for « $term »", Input::all());


    // Get sections
    $allSections = Section::all();
    $selected_sections = [];

    // Semesters
    $selected_semesters = [];
    $filter_semesters = Input::has('semesters') && Input::get('semesters') != 'all';
    if ($filter_semesters) {
      $joined_selected_semesters = Input::get('semesters');
      $selected_semesters = explode("-", $joined_selected_semesters);
    }
    else {
      $joined_selected_semesters = implode("-", Config::get('content.semesters'));
    }

    // Get student section
    $student_section_id = -1;
    if (Tequila::isLoggedIn()) {
      $studentSectionSlug = StudentInfo::getSection();
      $student_section_id = $allSections->first(function($i, $section) use($studentSectionSlug) {
        return $section->string_id == $studentSectionSlug;
      })->id;
    }

    // TODO : name en / fr
    $cleaned = DB::getPDO()->quote(preg_replace("/[^A-Za-z0-9èàé ]/", '', $term));
    $query = DB::table('courses')
          ->select(DB::raw('courses.name_fr as name, courses.id as id'))
          ->addSelect(DB::raw('CONCAT(teachers.firstname, " ", teachers.lastname) as teacher_fullname'))
          ->addSelect(DB::raw('courses.avg_overall_grade as avg_overall_grade'))
          ->addSelect(DB::raw('GROUP_CONCAT(study_plans.string_id) as plans'))
          ->addSelect(DB::raw('GROUP_CONCAT(course_study_plan.semester) as semesters'))
          ->addSelect(DB::raw('(select count(*) from reviews where course_id=courses.id) as reviewsCount'))
          ->addSelect(DB::raw("
            MATCH(courses.name_en, courses.name_fr)
            AGAINST ($cleaned IN NATURAL LANGUAGE MODE) as course_relevance
          "))
          ->addSelect(DB::raw("
            MATCH(teachers.firstname, teachers.lastname)
            AGAINST ($cleaned IN NATURAL LANGUAGE MODE) as teacher_relevance
          "))
          ->leftJoin('sections', 'sections.id', '=', 'courses.section_id')
          ->leftJoin('course_study_plan', 'course_study_plan.course_id', '=', 'courses.id')
          ->leftJoin('study_plans', 'course_study_plan.study_plan_id', '=', 'study_plans.id')
          ->leftJoin('teachers', 'teachers.id', '=', 'courses.teacher_id');

    $query->where(function($q) use($cleaned) {
      $q->whereRaw("MATCH(courses.name_en, courses.name_fr) AGAINST($cleaned IN NATURAL LANGUAGE MODE)");

      if (!Input::has('dont_match_teachers')) {
        $q->orWhereRaw("MATCH(teachers.firstname, teachers.lastname) AGAINST ($cleaned IN NATURAL LANGUAGE MODE)");
      }
    });

    if (Input::has('only_reviewed')) {
      $query->whereExists(function ($query) {
        $query->select(DB::raw(1))
            ->from('reviews')
            ->whereRaw('reviews.course_id = courses.id');
      });
    }

    if ($filter_semesters) {
      $query->whereIn('course_study_plan.semester', $selected_semesters);
    }

    $allowedSortingFields = ['courses.name', 'teachers.lastname', 'reviewsCount'];
    $order = Input::has('desc') ? 'desc' : 'asc';
    if (Input::has('sortby') && in_array($field = Input::get('sortby'), $allowedSortingFields)) {
      $query->orderBy(DB::raw($field), $order);
    }
    else {
      $query->orderBy(DB::raw('course_relevance + teacher_relevance'), 'desc' /*$order*/);
    }

    $query->groupBy('courses.id');


    $paginated = $query->paginate($nbPerPage);
    $courses = $paginated->getItems();
    $courses = array_map(function($c) { return (array) $c;}, $courses);

    /* From the SQL query, 'sections' and 'semesters' look like IN,SV,MT and BA1,BA1,BA3.
    We need to adapt them to a standard array */

    foreach($courses as &$course) {
      $plans = explode(",", $course['plans']);
      $semesters = explode(",", $course['semesters']);
      $plansData = [];
      foreach($plans as $i => $plan) {
        $plansData[] = [
          'string_id' => $plan,
          'semester' => $semesters[$i]
        ];
      }
      $course['plans'] = $plansData;
      $course['teacher'] = ['fullname' => $course['teacher_fullname']];
      unset($course['semesters']);
      unset($course['teacher_fullname']);
    }

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));

    $mp->track('Searched a course', [
      'keywords' => Input::get('q'),
      'number of results' => sizeof($courses)
    ]);

    return View::make('courses.search', [
      'page_title' => $term.' &ndash; Search courses',
      'paginator' => $paginated,
      'courses' => $courses,
      'sections' => $allSections,
      'joined_selected_semesters' => $joined_selected_semesters,
      'selected_semesters' => $selected_semesters,
      'was_filtered' => count(Input::except('page', 'q')) > 0,
      'student_section_id' => $student_section_id,
      'Locale' => LaravelLocalization::getCurrentLocale()
    ]);
  }
}
