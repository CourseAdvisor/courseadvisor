<?php
class StudyPlan extends Eloquent {
    protected $fillable = [];

    public function getNameAttribute() {
        return $this->name_en;
    }

    public function getUrlAttribute() {
        return $this->url_en;
    }

    public function courses() {
        return $this->belongsToMany('Course')->withPivot('semester');
    }

    public function studyCycle() {
        return $this->belongsTo('StudyCycle');
    }
}
