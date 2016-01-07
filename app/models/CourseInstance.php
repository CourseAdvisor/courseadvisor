<?php
class CourseInstance extends Eloquent {
  protected $table = 'course_instances';
  public $timestamps = false;

  // Cache reviews count
  private $_reviewsCount = null;

  protected $fillable = [
    'teacher_id', 'course_id', 'year', 'term', 'credits'
  ];

  public function course() {
    return $this->belongsTo('Course');
  }

  public function teacher() {
    return $this->belongsTo('Teacher');
  }

  public function reviews() {
    return $this->hasMany('Review');
  }
}
