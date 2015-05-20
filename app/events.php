<?php

Event::listen('review.newReview', function($review) {
	$data = [
		'courseUrl' => action('CourseController@show', [
			'slug' => Str::slug($review->course->name),
			'id' => $review->course->id
		]),
		'courseName' => $review->course->name,
		'studentName' => $review->student->fullname
	];

	Mail::send('emails.newReview', $data, function($message) {
		$message->to(StudentInfo::getAdminEmails())
				->from('server@courseadvisor.ch', "CourseAdvisor server")
				->subject("Un nouvel avis a été posté sur CourseAdvisor !");
	});

	if ($review->is_anonymous) {
		Event::fire('review.newAnonymous', [$review]);
	}
});

Event::listen('review.newAnonymous', function($review) {
	$data = [
		'moderationUrl' => action('AdminController@moderate'),
		'studentName' => $review->student->fullname,
		'courseName' => $review->course->name
	];
	Mail::send('emails.newAnonymousReview', $data, function($message) {
		$message->to(StudentInfo::getAdminEmails())
				->from('server@courseadvisor.ch', "CourseAdvisor server")
				->subject("Un avis est en attente de validation sur CourseAdvisor");
	});
});
