<?php
class Student extends Eloquent {

	// No need to automatically insert 'updated_at' and 'created_at' columns automatically
	public $timestamps = false;
	protected $table = 'students';

	protected $fillable = array(
		'firstname', 'lastname', 'email', 'semester', 'section_id', 'sciper'
	);

	public function section() {
		return $this->belongsTo('Section');
	}

	public function reviews() {
		return $this->hasMany('Review');
	}

	public function getFullnameAttribute() {
		return $this->firstname . " " . $this->lastname;
	}
}