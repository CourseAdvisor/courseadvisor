<?php
class ReviewController extends BaseController {


  public function createComment() {
    $validator = Comment::getValidator(Input::all());
    if ($validator->fails()) {
      return Redirect::to(URL::previous())
          ->withInput()
          ->withErrors($validator);
    }

    $comment = new Comment(Input::all());
    $comment->student_id = Session::get('student_id');

    $comment->save();

    return Redirect::to(URL::previous())->with('message', ['success', 'Your comment has been posted.']);
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
    }

    $commentable = ($target == 'review')
        ? Review::find($review_id)
        : Comment::find($comment_id);

    $commentable->updateScore();
    $commentable->save();

    if (!$cancelled) {
      $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
      $mp->track('Voted', [
        'Type' => $vote->type,
        'Target' => $target,
        'Review author' => $commentable->student->sciper,
        'Review' => $commentable->id
      ]);
    }

    return json_encode(array('score' => $commentable->score, 'cancelled' => $cancelled));
  }

}