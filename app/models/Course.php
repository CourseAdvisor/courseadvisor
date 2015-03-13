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

	public function teacher() {
		return $this->belongsTo('Teacher');
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

	public function updateAverages() {
		$sql  = "AVG(avg_grade) as total";
		$sql .= ", AVG(lectures_grade) as lectures";
		$sql .= ", AVG(exercises_grade) as exercises";
		$sql .= ", AVG(content_grade) as content";

		$averages = DB::table('reviews')
					->select(DB::raw($sql))
					->where('course_id', $this->id)->first();

		$this->avg_overall_grade = $averages->total;
		$this->avg_lectures_grade = $averages->lectures;
		$this->avg_exercises_grade = $averages->exercises;
		$this->avg_content_grade = $averages->content;

		$this->save();
	}
}