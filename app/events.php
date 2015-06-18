<?php

Event::listen('review.newReview', function($review) {
	if (Config::get('app.debug')) return;

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
	if (Config::get('app.debug')) return;

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

Event::listen('review.rejected', function($review, $reasons) {
	$data = [
		'review' => $review,
		'reasons' => $reasons
	];
	Mail::send('emails.rejectedReview', $data, function($message) use($review) {
		$message->to($review->student->email)
				->from('noreply@courseadvisor.ch', "CourseAdvisor")
				->subject("CourseAdvisor - ".trans('emails.title_rejected_review'));
	});
});

Event::listen('review.accepted', function($review) {
	Mail::send('emails.acceptedReview', ['review' => $review], function($message) use($review) {
		$message->to($review->student->email)
				->from('noreply@courseadvisor.ch', "CourseAdvisor")
				->subject("CourseAdvisor - ".trans('emails.title_accepted_review'));
	});
});