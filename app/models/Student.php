<?php
class Student extends Eloquent {

  protected $table = 'students';

  protected $fillable = array(
    'firstname', 'lastname', 'email', 'semester', 'section_id', 'sciper'
  );


  // Relations

  public function section() {
    return $this->belongsTo('Section');
  }

  public function reviews() {
    return $this->hasMany('Review');
  }

  public function inscriptions() {
    return $this->hasMany('Inscription', 'sciper', 'sciper');
  }

  public function studyPlans() {
    return $this->belongsToMany('StudyPlan');
  }


  // Attributes

  public function getFullnameAttribute() {
    return $this->firstname . " " . $this->lastname;
  }

  public function getPageURLAttribute() {
    return "http://people.epfl.ch/".$this->sciper;
  }

  public function isRegistered($course_id) {
    return $this->inscriptions()->where('course_id', $course_id)->first() != null;
  }

    public function refreshPlans($section_id = null) {

        if ($section_id == null) {
            $section_id = $this->section->string_id;
        }

        $plans = StudyPlan::where('string_id', $section_id)
            ->where('study_cycle_id', StudentInfo::getStudyCycle())
            ->lists('id');

        if ($plans) {
            $this->studyPlans()->sync($plans);
        }
    }
}