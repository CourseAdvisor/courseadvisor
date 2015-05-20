<?php
class Inscription extends Eloquent {
  protected $fillable = ['course_id', 'year', 'term', 'sciper'];

  public $timestamps = false;

  public function course() {
    return $this->belongsTo('Course');
  }

  public function student() {
    return $this->belongsTo('Student', 'sciper', 'sciper');
  }
}
