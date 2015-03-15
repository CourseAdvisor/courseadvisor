<?php

class SearchController extends Controller {
	public function search() {
		if (!Input::has('q')) {
			return Redirect::to('/');
		}


		$nbPerPage = Config::get('app.searchNbCoursesPerPage');

		$terms = mysql_real_escape_string(Input::get('q'));
		$showOnlyReviewed = Input::has('only_reviewed');

		$courses = Course::with('teacher', 'sections');

		$sqlWhere = "(`name` LIKE '%$terms%'";
		$sqlWhere .= " OR (select count(*) from `teachers` where `courses`.`teacher_id` = `teachers`.`id` and `lastname` LIKE '%$terms%') >= 1)";

		if ($showOnlyReviewed) {
			$sqlWhere .= ' AND (select count(*) from `reviews` where `reviews`.`course_id` = `courses`.`id`) >= 1 ';
		}

		$sectionIds = [];
		if(Input::has('only_sections')) {
			$sectionIds = array_map('intval', array_keys(Input::get('only_sections')));
			$sectionIdsStr = implode(", ", $sectionIds);
			$sqlWhere .= "AND (select count(*) from `course_section` where `course_id` = `courses`.`id` and `section_id` IN ($sectionIdsStr)) >= 1";
		}
		$courses = $courses->whereRaw($sqlWhere);
		$courses = $courses->paginate($nbPerPage);

		return View::make('courses.search', [
			'courses' => $courses,
			'sections' => Section::all(),
			'sectionIds' => $sectionIds
		]);
	}
}
