<?php

class Commentable extends Eloquent {

  // =======================
  // ==== Voting system ====
  // =======================

  public function votes() {
    return $this->hasMany('Vote');
  }

  public function hasUpVote($student_id) {
    return $this->hasVote('up', $student_id);
  }
  public function hasDownVote($student_id) {
    return $this->hasVote('down', $student_id);
  }
  public function hasVote($type, $student_id) {
    return $this->votes()->where(array('student_id' => $student_id, 'type' => $type))->first() != null;
  }

  public function updateScore() {
    $score = 0;
    foreach($this->votes as $vote) {
      if ($vote->isUp()) {
        $score++;
      } else {
        $score--;
      }
    }
    $this->score = $score;
  }


  // =======================
  // === Comments system ===
  // =======================

  public function comments() {
    return $this->hasMany('Comment', $this->comment_key);
  }
}