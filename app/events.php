<?php

Event::listen('review.newReview', function($review) {
  if (Config::get('app.skip_mails')) return;

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
        ->from('noreply@courseadvisor.ch', "CourseAdvisor")
        ->subject("Un nouvel avis a été posté sur CourseAdvisor !");
  });

  if ($review->is_anonymous) {
    Event::fire('review.newAnonymous', [$review]);
  }
});

Event::listen('review.newAnonymous', function($review) {
  if (Config::get('app.skip_mails')) return;

  $data = [
    'moderationUrl' => action('AdminController@moderate'),
    'studentName' => $review->student->fullname,
    'courseName' => $review->course->name
  ];
  Mail::send('emails.newAnonymousReview', $data, function($message) {
    $message->to(StudentInfo::getAdminEmails())
        ->from('noreply@courseadvisor.ch', "CourseAdvisor")
        ->subject("Un avis est en attente de validation sur CourseAdvisor");
  });
});

Event::listen('review.rejected', function($review, $reasons) {
  if (Config::get('app.skip_mails')) return;
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
  if (Config::get('app.skip_mails')) return;
  Mail::send('emails.acceptedReview', ['review' => $review], function($message) use($review) {
    $message->to($review->student->email)
        ->from('noreply@courseadvisor.ch', "CourseAdvisor")
        ->subject("CourseAdvisor - ".trans('emails.title_accepted_review'));
  });
});

Event::listen('comment.newComment', function($comment) {

  if (Config::get('app.skip_mails')) return;

  $review = $comment->review;
  $parent = $comment->parent;
  if ($parent)
    $target = $parent->student;
  else
    $target = $review->student;

  // Don't do anything if user posting the comment is the same as the target
  if ($target->id == $comment->student_id) return;

  $data = [
    'who' => $comment->student,
    'comment' => $comment,
    'parent' => $parent,
    'review' => $review
  ];

  if (!$parent) { // Comment on review
    Mail::send('emails.newCommentOnReview', $data, function($message) use($target, $comment) {
      $message->to($target->email)
          ->from('noreply@courseadvisor.ch', "CourseAdvisor")
          ->subject("{$comment->student->fullname} commented your review!");
    });
  } else { // Comment on comment
    Mail::send('emails.newCommentOnComment', $data, function($message) use($target, $comment) {
      $message->to($target->email)
          ->from('noreply@courseadvisor.ch', "CourseAdvisor")
          ->subject("New reply from {$comment->student->fullname}");
    });
  }

});
