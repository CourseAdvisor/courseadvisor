<?php
class StudyCycle extends Eloquent {
    protected $fillable = [];

    public function getNameAttribute() {
        return $this->name_en;
    }
}
