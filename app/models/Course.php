<?php
class Course extends Eloquent {
	protected $table = 'courses';
	public $timestamps = false;

	protected $fillable = [
		'name', 'string_id'
	];

	public function students() {
		return $this->belongsToMany('Student');
	}

	public function sections() {
		return $this->belongsToMany('Section')->withPivot('semester');
	}
}