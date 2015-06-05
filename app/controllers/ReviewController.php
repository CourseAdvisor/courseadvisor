<?php
class ReviewController extends BaseController {


  public function vote() {

    $review_id = Input::get('review');
    $student_id = Session::get('student_id');

    $vote = Vote::where([
      'review_id' => $review_id,
      'student_id' => $student_id
    ])->first();

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
        'student_id' => $student_id
      ]);
      $vote->save();
    }

    $review = Review::find($review_id);
    $review->updateScore();
    $review->save();

    if (!$cancelled) {
      $mp = Mixpanel::getInstance(Config::get('app.mixpanel_key'));
      $mp->track('Voted a review', [
        'Course name' => $review->course->name,
        'Course' => $review->course->id,
        'Type' => $vote->type,
        'Review author' => $review->student->sciper,
        'Review' => $review->id
      ]);
    }

    return json_encode(array('score' => $review->score, 'cancelled' => $cancelled));
  }

}