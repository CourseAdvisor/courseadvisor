<?php
class Section extends Eloquent {
	protected $fillable = ['string_id', 'name_en', 'name_fr'];

	public function students() {
		return $this->hasMany('Student');
	}

	public function courses() {
		return $this->belongsToMany('Course')->withPivot('semester');
	}

    public function getNameAttribute() {
        return $this->name_en;
    }
}
