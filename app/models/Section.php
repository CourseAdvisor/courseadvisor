<?php
class Section extends Eloquent {
  protected $fillable = ['string_id', 'name_en', 'name_fr'];

  public function students() {
    return $this->hasMany('Student');
  }

  public function courses() {
    return $this->hasToMany('Course');
  }

  public function getNameAttribute() {
    if (LaravelLocalization::getCurrentLocale() == 'fr')
      return $this->name_fr;
    else
      return $this->name_en;
  }
}
