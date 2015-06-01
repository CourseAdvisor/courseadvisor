<?php
class Vote extends Eloquent {
  protected $table = 'votes';

  protected $fillable = [
    'student_id', 'review_id', 'type'
  ];

  public function student() {
    $this->belongsTo('Student');
  }

  public function review() {
    $this->belongsTo('Review');
  }

  public function isUp() {
    return $this->type == 'up';
  }

  public function isDown() {
    return $this->type != 'up';
  }
}