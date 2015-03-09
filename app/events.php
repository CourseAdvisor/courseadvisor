<?php

Event::listen('course.newReview', function($course) {
	// Compute total average
	$sql  = "AVG(avg_grade) as total";
	$sql .= ", AVG(lectures_grade) as lectures";
	$sql .= ", AVG(exercises_grade) as exercises";
	$sql .= ", AVG(content_grade) as content";

	$averages = DB::table('reviews')
				->select(DB::raw($sql))
				->where('course_id', $course->id)->first();
	
	$course->avg_overall_grade = $averages->total;
	$course->avg_lectures_grade = $averages->lectures;
	$course->avg_exercises_grade = $averages->exercises;
	$course->avg_content_grade = $averages->content;

	$course->save();
});