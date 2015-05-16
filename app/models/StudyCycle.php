<?php
class StudyCycle extends Eloquent {
  protected $fillable = [];

  public function plans() {
    return $this->hasMany('StudyPlan');
  }

  public function getNameAttribute() {
    if (LaravelLocalization::getCurrentLocale() == 'fr')
      return $this->name_fr;
    else
      return $this->name_en;
  }
}
