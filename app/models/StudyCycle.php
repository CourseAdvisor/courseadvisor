<?php
class StudyCycle extends Eloquent {
    protected $fillable = [];

    public function plans() {
        return $this->hasMany('StudyPlan');
    }

    public function getNameAttribute() {
        return $this->name_en;
    }
}
