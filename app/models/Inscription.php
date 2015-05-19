<?php
class Inscription extends Eloquent {
  protected $fillable = ['course_id', 'year', 'term', 'sciper'];

  public $timestamps = false;

  public function courses() {
    return $this->hasToMany('Course');
  }
}
