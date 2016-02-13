<?php
class ReviewController extends BaseController {

  public function createReview() {
    $validator = Review::getValidator(Input::all());
    if ($validator->fails()) {
      return Redirect::to(URL::previous() . "#my-review")
          ->withInput()
          ->withErrors($validator);
    }

    // Get course and student info
    $courseInstance = CourseInstance::with('course')->findOrFail(Input::get('course_instance_id')); // Fails if the course doesn't exist
    $studentId = Session::get('student_id');
    $goToCourse = Redirect::action('CourseController@show', [$courseInstance->course->slug, $courseInstance->course->id]);

    // Check if the course was not already reviewed by the student
    if($courseInstance->course->alreadyReviewedBy($studentId)) {
      return $goToCourse->with('message', ['danger', trans('courses.review-create-not-allowed')]);
    }

    // Create the review
    $newReview = new Review(Input::all());
    $newReview->course_instance_id = $courseInstance->id;
    $newReview->student_id = $studentId;
    $newReview->updateAverage();


    // Check if we should use 'mobile_difficulty'
    if (Input::has('difficulty_mobile') && Input::get('difficulty_mobile') != 0) {
      $newReview->difficulty = Input::get('difficulty_mobile');
    }

    if(Input::get('anonymous') == true) {
      $newReview->is_anonymous = 1;
      $newReview->status = 'waiting';
    }

    $newReview->save();
    Event::fire('review.newReview', [$newReview]);

    // Update averages only if the review is not anonymous
    if (!$newReview->is_anonymous) {
      $courseInstance->course->updateAverages();
      $msg = trans('courses.review-posted-message');
    }
    else {
      $msg = trans('courses.review-posted-anonymous-message');
    }

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Posted a review', [
        'Course name' => $courseInstance->course->name,
        'Average grade' => $newReview->avg_grade,
        'Exercises grade' => $newReview->exercises_grade,
        'Lectures grade' => $newReview->lectures_grade,
        'Content grade' => $newReview->content_grade,
        'Difficulty' => $newReview->difficulty,
        'Is review' => $newReview->isReview(),
        'Anonymous' => $newReview->is_anonymous == 1,
        'Locale' => LaravelLocalization::getCurrentLocale()
    ]);

    return $goToCourse->with('message', ['success', $msg]);
  }

  public function updateReview() {
    if (!Input::has('review_id')) {
      return App::abort(400, "No review to edit");
    }

    // Retrieve review
    $review = Review::findOrFail(Input::get('review_id'));
    $courseRedirect = Redirect::action('CourseController@show', [$review->course->slug, $review->course->id]);

    // Check authorized
    if ($review->student_id != StudentInfo::getId()) {
      return $courseRedirect
        ->with('message', ['danger', trans('courses.review-update-not-allowed')]);
    }

    // Check input data
    $validator = Review::getValidator(Input::all());
    if ($validator->fails()) {
      return Redirect::to(LaravelLocalization::getLocalizedURL(
            LaravelLocalization::getCurrentLocale(),
            action('CourseController@show', [$review->course->slug, $review->course->id])) . '#!xedit-' . $review->id)
        ->withInput()
        ->withErrors($validator);
    }

    $review->comment = Input::get('comment');
    $review->title = Input::get('title');
    $review->lectures_grade = Input::get('lectures_grade');
    $review->exercises_grade = Input::get('exercises_grade');
    $review->content_grade = Input::get('content_grade');
    $review->difficulty = Input::get('difficulty');

    $msg = trans('courses.review-updated-message');

    if (Input::get('anonymous') == true) {
        $review->is_anonymous = 1;
        $review->status = 'waiting';
        $msg = trans('courses.review-updated-anonymous-message');
    } else {
      $review->is_anonymous = 0;
    }

    $review->updateAverage();
    $review->save();

    if ($review->is_anonymous) {
      Event::fire('review.newAnonymous', [$review]);
    }

    $review->course->updateAverages();

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Updated a review', [
      'Course name' => $review->course->name,
      'Average grade' => $review->avg_grade,
      'Exercises grade' => $review->exercises_grade,
      'Lectures grade' => $review->lectures_grade,
      'Content grade' => $review->content_grade,
      'Difficulty' => $review->difficulty,
      'Is review' => $review->isReview(),
      'Anonymous' => $review->is_anonymous == 1,
      'Locale' => LaravelLocalization::getCurrentLocale()
    ]);

    return $courseRedirect
        ->with('message', ['success', $msg]);
  }

  public function deleteReview() {
    $review = Review::findOrFail(Input::get('review_id'));

    $courseRedirect = Redirect::action('CourseController@show', [$review->course->slug, $review->course->id]);

    // Check authorized
    if ($review->student_id != StudentInfo::getId()) {
      return $courseRedirect
        ->with('message', ['danger', trans('courses.review-delete-not-allowed')]);
    }

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Deleted a review', [
        'Course name' => $review->course->name,
        'Comments' => count($review->comments),
        'Average grade' => $review->avg_grade,
        'Exercises grade' => $review->exercises_grade,
        'Lectures grade' => $review->lectures_grade,
        'Content grade' => $review->content_grade,
        'Difficulty' => $review->difficulty,
        'Is review' => $review->isReview(),
        'Anonymous' => $review->is_anonymous == 1,
        'Locale' => LaravelLocalization::getCurrentLocale()
    ]);

    $review->delete();
    $review->course->updateAverages();

    return $courseRedirect
      ->with('message', ['success', trans('courses.review-deleted-message')]);
  }

  public function createComment() {

    $comment = new Comment([
      'body' => Input::get('body'),
      'review_id' => Input::get('review_id'),
      'parent_id' => Input::get('parent_id') ? Input::get('parent_id') : null
    ]);
    $comment->student_id = Session::get('student_id');

    if ($comment->parent_id) { // Has parent
      // Inherit review id
      $comment->review_id = Comment::findOrFail($comment->parent_id)->review_id;
    } else {
      $comment->review_id = Input::get('review_id');
    }

    $validator = Comment::getValidator($comment->toArray());
    if ($validator->fails()) {
      Session::flash('error-comment', [
          'action' => 'create',
          'root' => $comment->review_id,
          'parent' => $comment->parent_id]);

      $hash = ($comment->parent_id) ? '#comment-'.$comment->parent_id : '#review-'.$comment->review_id;
      return Redirect::to(URL::previous().$hash)
          ->withInput()
          ->withErrors($validator);
    }
    $comment->save();

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Posted a comment', [
        'Owner' => $comment->student_id,
        'Review' => $comment->review_id,
        'Parent' => $comment->parent_id
    ]);

    Event::fire('comment.newComment', [$comment]);

    return Redirect::to(URL::previous())->with('message', ['success', trans('courses.comment-posted-confirm')]);
  }

  public function updateComment() {
    $id = Input::get('comment_id');
    $comment = Comment::findOrFail($id);

    if ($comment->student_id != StudentInfo::getId()) {
      return Redirect::to(URL::previous())->with('message', ['danger', trans('courses.comment-update-unauthorized')]);
    }

    $comment->body = Input::get('body');

    $validator = Comment::getValidator($comment->toArray());
    if ($validator->fails()) {
      Session::flash('error-comment', [
          'action' => 'edit',
          'root' => $comment->review_id,
          'parent' => $id]);

      return Redirect::to(URL::previous().'#comment-'.$id)
          ->withInput()
          ->withErrors($validator);
    }

    $comment->save();

    return Redirect::to(URL::previous())->with('message', ['success', trans('courses.comment-updated-confirm')]);
  }

  public function deleteComment() {
    $id = Input::get('comment_id');
    $comment = Comment::findOrFail($id);

    if ($comment->student_id != StudentInfo::getId()) {
      return Redirect::to(URL::previous())->with('message', ['danger', trans('courses.comment-delete-unauthorized')]);
    }

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Deleted a comment', [
        'Children' => count($comment->comments),
        'Owner' => $comment->student_id,
        'Review' => $comment->review_id,
        'Parent' => $comment->parent_id
    ]);

    $comment->delete();

    return Redirect::to(URL::previous())->with('message', ['success', trans('courses.comment-deleted-confirm')]);
  }

  public function vote() {

    $review_id = Input::get('review', null);
    $comment_id = Input::get('comment', null);
    $type = Input::get('type');
    $student_id = Session::get('student_id');

    if ($type != 'up' && $type != 'down') {
      return Response::make('bad request', 400);
    }

    // Determines target (review or comment)
    $target = 'review';
    if (($review_id != null && $comment_id != null) ||
        ($review_id == null && $comment_id == null)) {
      return Response::make('bad request', 400);
    } else if($comment_id != null) {
      $target = 'comment';
    }

    $commentable = ($target == 'review')
        ? Review::find($review_id)
        : Comment::find($comment_id);

    if ($commentable == null) {
      return Response::make('bad request', 400);
    }

    $vote = Vote::where([
          'student_id' => $student_id,
          'review_id' => $review_id,
          'comment_id' => $comment_id
        ])
        ->first();

    $cancelled = false;
    if ($vote != null) {
      if ($vote->type == $type) {
        // Cancel vote
        $vote->delete();
        $cancelled = true;
      } else {
        // Just need to update
        $vote->type = $type;
        $vote->save();
      }
    } else {
      // Create a new one
      $vote = new Vote([
        'type' => $type,
        'review_id' => $review_id,
        'comment_id' => $comment_id,
        'student_id' => $student_id
      ]);
      $vote->save();

      // Only track new votes
      $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
      $mp->track('Voted', [
        'Type' => $vote->type,
        'Target' => $target,
        'Review author' => $commentable->student->sciper,
        'Review' => $commentable->id
      ]);
    }


    $commentable->updateScore();
    $commentable->save();

    return json_encode(array('score' => $commentable->score, 'cancelled' => $cancelled));
  }
}
