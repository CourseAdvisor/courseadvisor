<?php
class Review extends Eloquent {
	protected $table = 'reviews';

	protected $fillable = ['lectures_grade', 'exercises_grade', 'content_grade', 'difficulty', 'title', 'comment'];

	public function student() {
		return $this->belongsTo('Student');
	}

	public function course() {
		return $this->belongsTo('Course');
	}

	public function updateAverage() {
		$this->avg_grade = 1/3 * ($this->lectures_grade + $this->exercises_grade + $this->content_grade);
	}

	public function scopePublished($q) {
		return $q->whereIn('status', ['accepted', 'published']);
	}

	public function scopeWaiting($q) {
		return $q->where('status', 'waiting');
	}

	public function scopeRejected($q) {
		return $q->where('status', 'rejected');
	}

	public function scopeAccepted($q) {
		return $q->where('status', 'accepted');
	}

	public static function rules() {
		$gradeRule = 'required|integer|between:1,5';

		return [
			'lectures_grade' => $gradeRule,
			'exercises_grade' => $gradeRule,
			'content_grade' => $gradeRule,
			'difficulty' => 'integer|between:0,5',
			'title' => 'required|max:50',
			'comment' => 'required|min:20'
		];
	}
}