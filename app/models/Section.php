<?php
class Section extends Eloquent {
	protected $fillable = ['string_id', 'name'];

	public function students() {
		return $this->hasMany('Student');
	}

	public function courses() {
		return $this->belongsToMany('Course');
	}
}