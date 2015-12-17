<?php
class Teacher extends Eloquent {
  protected $fillable = ['lastname', 'firstname', 'sciper'];

  public function courseInstances() {
    return $this->hasMany('CourseInstance');
  }

  public function getCoursesAttribute() {
    return $this->courseInstances->map(function($inst) {
      return $inst->course;
    })->unique();
  }

  public function getFullnameAttribute() {
    return $this->firstname . " " . $this->lastname;
  }

  public function getPeoplePageLinkAttribute() {
    return 'http://people.epfl.ch/'.$this->sciper;
  }
}
