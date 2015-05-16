<?php
class StudyPlan extends Eloquent {
  protected $fillable = [];

  public function getNameAttribute() {
    if (LaravelLocalization::getCurrentLocale() == 'fr')
      return $this->name_fr;
    else
      return $this->name_en;
  }

  public function getUrlAttribute() {
    if (LaravelLocalization::getCurrentLocale() == 'fr')
      return $this->url_fr;
    else
      return $this->url_en;
  }

  public function courses() {
    return $this->belongsToMany('Course')->withPivot('semester');
  }

  public function studyCycle() {
    return $this->belongsTo('StudyCycle');
  }
}
