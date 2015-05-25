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
		$total = 0;
		$count = 0;
		foreach(['exercises', 'lectures', 'content'] as $category) {
			$grade = $this->{$category . '_grade'};
			if ($grade != 0) {
				++$count;
				$total += $grade;
			}
		}

		$this->avg_grade = $count == 0 ? 0 : $total / $count;
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
		return [
			'lectures_grade' => 'integer|between:0,5',
			'exercises_grade' => 'integer|between:0,5',
			'content_grade' =>'integer|between:0,5',
			'difficulty' => 'integer|between:0,5',
			'title' => 'required|max:100',
			'comment' => 'required|min:20'
		];
	}

	public static function getValidator($data) {
		$v = Validator::make($data, self::rules());

		$v->sometimes('lectures_grade', ['required', 'not_in:0'], function ($input) {
			return empty($input->content_grade) && empty($input->exercises_grade);
		});

		$v->sometimes('content_grade', ['required', 'not_in:0'], function ($input) {
			return empty($input->lectures_grade) && empty($input->exercises_grade);
		});

		$v->sometimes('exercises_grade', ['required', 'not_in:0'], function ($input) {
			return empty($input->content_grade) && empty($input->lectures_grade);
		});

		return $v;
	}
}