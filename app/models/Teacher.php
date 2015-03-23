<?php
class Teacher extends Eloquent {
  protected $fillable = ['lastname', 'firstname', 'sciper'];

  public function courses() {
    return $this->hasMany('Course');
  }

  public function getFullnameAttribute() {
    return $this->firstname . " " . $this->lastname;
  }

  public function getPeoplePageLinkAttribute() {
    return 'http://people.epfl.ch/'.$this->sciper;
  }
}
