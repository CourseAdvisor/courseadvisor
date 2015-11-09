<?php
class ReviewController extends BaseController {


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
    $student_id = Session::get('student_id');

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

    $vote = Vote::where([
          'student_id' => $student_id,
          'review_id' => $review_id,
          'comment_id' => $comment_id
        ])
        ->first();

    $cancelled = false;
    if ($vote != null) {
      if ($vote->type == Input::get('type')) {
        // Cancel vote
        $vote->delete();
        $cancelled = true;
      } else {
        // Just need to update
        $vote->type = Input::get('type');
        $vote->save();
      }
    } else {
      // Create a new one
      $vote = new Vote([
        'type' => Input::get('type'),
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
