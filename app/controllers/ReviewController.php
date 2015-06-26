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
      return Redirect::to(URL::previous())
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

    return Redirect::to(URL::previous())->with('message', ['success', 'Your comment has been posted.']);
  }

  public function updateComment() {
    $id = Input::get('comment_id');
    $comment = Comment::findOrFail($id);

    if ($comment->student_id != StudentInfo::getId()) {
      return Redirect::to(URL::previous())->with('message', ['danger', 'Cannot update this comment.']);
    }

    $comment->body = Input::get('body');

    $validator = Comment::getValidator($comment->toArray());
    if ($validator->fails()) {
      return Redirect::to(URL::previous())
          ->withInput()
          ->withErrors($validator);
    }

    $comment->save();

    return Redirect::to(URL::previous())->with('message', ['success', 'Your comment has been edited.']);
  }

  public function deleteComment() {
    $id = Input::get('comment_id');
    $comment = Comment::findOrFail($id);

    if ($comment->student_id != StudentInfo::getId()) {
      return Redirect::to(URL::previous())->with('message', ['danger', 'Cannot delete this comment.']);
    }

    $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
    $mp->track('Deleted a comment', [
        'Children' => count($comment->comments),
        'Owner' => $comment->student_id,
        'Review' => $comment->review_id,
        'Parent' => $comment->parent_id
    ]);

    $comment->delete();

    return Redirect::to(URL::previous())->with('message', ['success', 'Your comment has been deleted.']);
  }

  public function vote() {

    $review_id = Input::get('review');
    $comment_id = Input::get('comment');
    $student_id = Session::get('student_id');

    // Determines target (review or comment)
    $target = 'review';
    if ((empty($review_id) && empty($comment_id)) ||
        (!empty($review_id) && !empty($comment_id))) {
      return Response::make('bad request', 400);
    } else if(!empty($comment_id)) {
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