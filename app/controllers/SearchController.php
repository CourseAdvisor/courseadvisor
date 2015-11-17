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
    $cleaned = DB::getPDO()->quote(preg_replace("/[^A-Za-z0-9èàéô ]/", '', $term));
    $query = DB::table('courses')
          ->select(DB::raw('courses.id as course_id'))
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

    $allowedSortingFields = ['courses.name_fr', 'courses.name_en', 'teachers.lastname', 'reviewsCount'];
    $order = Input::has('desc') ? 'desc' : 'asc';
    $inverseOrder = $order == 'desc' ? 'asc' : 'desc';
    if (Input::has('sortby') && in_array($field = Input::get('sortby'), $allowedSortingFields)) {
      if ($field == 'reviewsCount') {
        $query->orderBy(DB::raw('(select count(*) from reviews R where R.course_id = courses.id)'), $inverseOrder);
      }
      else {
        $query->orderBy(DB::raw($field), $order);
      }
    }
    else {
      $query->orderBy(DB::raw('course_relevance + teacher_relevance'), $inverseOrder);
    }

    $query->groupBy('course_id');
    $paginated = $query->paginate($nbPerPage);

    $course_ids = array_map(function($c) { return $c->course_id; }, $paginated->getItems());
    if (empty($course_ids)) {
      $courses = [];
    }
    else {
      $courses = Course::whereIn('id', $course_ids)
        ->orderBy(DB::raw('FIELD(`id`, '. implode(', ', $course_ids) . ')'))
        ->get();
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
