<?php
class Course extends Eloquent {
	protected $table = 'courses';
	public $timestamps = false;

	protected $fillable = [
		'name', 'string_id', 'teacher'
	];

	public function sections() {
		return $this->belongsToMany('Section')->withPivot('semester');
	}

	public function reviews() {
		return $this->hasMany('Review');
	}

	public function alreadyReviewedBy($student_id) {
		if(Config::get('app.debug')) {
			return false;
		}
		$review = $this->reviews->first(function($num, $review) use($student_id) {
				return $review->student_id == $student_id;
		}, false);

		return $review;
	}
}