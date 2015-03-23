<?php

class SearchController extends Controller {
	public function search() {
		if (!Input::has('q')) {
			return Redirect::to('/');
		}
		$term = Input::get('q');
		$nbPerPage = Config::get('app.searchNbCoursesPerPage');
		$query = DB::table('courses')
					->select(DB::raw('courses.name as name, courses.id as id'))
					->addSelect(DB::raw('CONCAT(teachers.firstname, " ", teachers.lastname) as teacher_fullname'))
					->addSelect(DB::raw('courses.avg_overall_grade as avg_overall_grade'))
					->addSelect(DB::raw('GROUP_CONCAT(sections.string_id) as sections'))
					->addSelect(DB::raw('GROUP_CONCAT(course_section.semester) as semesters'))
					->addSelect(DB::raw('(select count(*) from reviews where course_id=courses.id) as reviewsCount'))
					->leftJoin('course_section', 'course_section.course_id', '=', 'courses.id')
					->leftJoin('sections', 'course_section.section_id', '=', 'sections.id')
					->leftJoin('teachers', 'teachers.id', '=', 'courses.teacher_id')
					->where('courses.name', 'LIKE', "%$term%"); // <-- Note : this is safe

		if (Input::has('only_reviewed')) {
			$query->whereExists(function ($query) {
				$query->select(DB::raw(1))
						->from('reviews')
						->whereRaw('reviews.course_id=courses.id');
			});
		}

		$selected_sections = [];
		if (Input::has('sections')) {
			$selected_sections = array_keys(Input::get('sections'));
			$query->whereIn('sections.id', $selected_sections);
		}

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
			'sections' => Section::all(),
			'selected_sections' => $selected_sections,
			'was_filtered' => Input::has('only_reviewed')
		]);
	}
}
