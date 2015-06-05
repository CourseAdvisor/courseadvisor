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

  public function votes() {
  	return $this->hasMany('Vote');
  }

  public function hasUpVote($student_id) {
    return $this->hasVote('up', $student_id);
  }
  public function hasDownVote($student_id) {
    return $this->hasVote('down', $student_id);
  }
  public function hasVote($type, $student_id) {
    return $this->votes()->where(array('student_id' => $student_id, 'type' => $type))->first() != null;
  }

  public function isReview() {
    return !empty($this->title);
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

  public function updateScore() {
    $score = 0;
    foreach($this->votes as $vote) {
      if ($vote->isUp()) {
        $score++;
      } else {
        $score--;
      }
    }
    $this->score = $score;
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

  /* overrides */
  public function save(array $options = []) {
    if ($this->exercises_grade == 0)
      $this->exercises_grade = null;

    if ($this->lectures_grade == 0)
      $this->lectures_grade = null;

    if ($this->content_grade == 0)
      $this->content_grade = null;

    if ($this->difficulty == 0)
      $this->difficulty = null;

    parent::save($options);
  }

  public static function rules() {
    return [
      'lectures_grade' => 'integer|between:0,5',
      'exercises_grade' => 'integer|between:0,5',
      'content_grade' =>'integer|between:0,5',
      'difficulty' => 'integer|between:0,5'
      /*,
      'title' => 'required|max:100',
      'comment' => 'required|min:20'
      */
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

    $v->sometimes('title', ['required'], function($input) {
      return !empty($input->comment);
    });

    return $v;
  }
}