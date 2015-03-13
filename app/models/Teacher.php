<?php
class Teacher extends Eloquent {
  protected $fillable = ['lastname', 'firstname', 'sciper'];

  public function courses() {
    return $this->hasMany('Course');
  }

  public function fullname() {
    return $this->firstname . " " . $this->lastname;
  }
}