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
		$filter_sections = Input::has('sections') && Input::get('sections') != 'all';
		if ($filter_sections) {
			$joined_selected_sections = Input::get('sections');
			$selected_sections = explode("-", $joined_selected_sections);
		}
		else {
			$joined_selected_sections = $allSections->reduce(function ($acc, $new) {
				if (is_null($acc)) return $new->id;
				return $acc . "-" . $new->id;
			});
		}

		// Get student section
		$student_section_id = -1;
		if (Tequila::isLoggedIn()) {
			$studentSectionSlug = StudentInfo::getSection();
			$student_section_id = $allSections->first(function($i, $section) use($studentSectionSlug) {
				return $section->string_id == $studentSectionSlug;
			})->id;
		}

		$cleaned = DB::getPDO()->quote(preg_replace("/[^A-Za-z0-9èàé ]/", '', $term));
		$query = DB::table('courses')
					->select(DB::raw('courses.name as name, courses.id as id'))
					->addSelect(DB::raw('CONCAT(teachers.firstname, " ", teachers.lastname) as teacher_fullname'))
					->addSelect(DB::raw('courses.avg_overall_grade as avg_overall_grade'))
					->addSelect(DB::raw('GROUP_CONCAT(sections.string_id) as sections'))
					->addSelect(DB::raw('GROUP_CONCAT(course_section.semester) as semesters'))
					->addSelect(DB::raw('(select count(*) from reviews where course_id=courses.id) as reviewsCount'))
					->addSelect(DB::raw("
						MATCH(courses.name)
						AGAINST ($cleaned IN NATURAL LANGUAGE MODE) as course_relevance
					"))
					->addSelect(DB::raw("
						MATCH(teachers.firstname, teachers.lastname)
						AGAINST ($cleaned IN NATURAL LANGUAGE MODE) as teacher_relevance
					"))
					->leftJoin('course_section', 'course_section.course_id', '=', 'courses.id')
					->leftJoin('sections', 'course_section.section_id', '=', 'sections.id')
					->leftJoin('teachers', 'teachers.id', '=', 'courses.teacher_id');

		$query->where(function($q) use($cleaned) {
			$q->whereRaw("MATCH(courses.name) AGAINST($cleaned IN NATURAL LANGUAGE MODE)");

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

		if ($filter_sections) {
			$query->whereIn('sections.id', $selected_sections);
		}


		$query->orderBy(DB::raw('2 * course_relevance + teacher_relevance'), false);
		$query->groupBy('courses.id');


		$paginated = $query->paginate($nbPerPage);
		$courses = $paginated->getItems();
		$courses = array_map(function($c) { return (array) $c;}, $courses);

		/* From the SQL query, 'sections' and 'semesters' look like IN,SV,MT and BA1,BA1,BA3.
		We need to adapt them to a standard array */
		foreach($courses as &$course) {
			$sections = explode(",", $course['sections']);
			$semesters = explode(",", $course['semesters']);
			$sectionsData = [];
			foreach($sections as $i => $section) {
				$sectionsData[] = [
					'string_id' => $section,
					'semester' => $semesters[$i]
				];
			}
			$course['sections'] = $sectionsData;
			$course['teacher'] = ['fullname' => $course['teacher_fullname']];
			unset($course['semesters']);
			unset($course['teacher_fullname']);
		}

		return View::make('courses.search', [
			'paginator' => $paginated,
			'courses' => $courses,
			'sections' => $allSections,
			'joined_selected_sections' => $joined_selected_sections,
			'selected_sections' => $selected_sections,
			'was_filtered' => count(Input::all()) > 1,
			'student_section_id' => $student_section_id
		]);
	}
}
