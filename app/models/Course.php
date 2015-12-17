<?php
class Course extends Eloquent {
  protected $table = 'courses';
  public $timestamps = false;

  // Cache reviews count
  private $_reviewsCount = null;

  protected $fillable = [
    'name_fr', 'name_en', 'string_id', 'url_fr', 'url_en', 'description', 'section_id'
  ];

  protected $appends = ['reviewsCount'];

  public function section() {
    return $this->belongsTo('Section');
  }

  public function plans() {
    return $this->belongsToMany('StudyPlan')->withPivot('semester');
  }

  public function instances() {
    return $this->hasMany('CourseInstance');
  }

  public function reviews() {
    return $this->hasManyThrough('Review', 'CourseInstance');
  }

  public function getNameAttribute() {
    if (LaravelLocalization::getCurrentLocale() == 'fr')
      return $this->name_fr;
    else
      return $this->name_en;
  }

  public function getUrlAttribute() {
    if (LaravelLocalization::getCurrentLocale() == 'fr')
      return $this->url_fr;
    else
      return $this->url_en;
  }

  public function getCurrentInstanceAttribute() {
    return $this->instances()->first();
  }

  public function alreadyReviewedBy($student_id) {
    $review = $this->instances->first(function($num, $inst) use ($student_id) {
      return $inst->reviews->first(function($num, $review) use($student_id) {
        return $review->student_id == $student_id;
      }, false);
    });

    return $review;
  }

  public function getSlugAttribute() {
    return Str::slug($this->name);
  }

  public function getReviewsCountAttribute() {
    if ($this->_reviewsCount == null) {
      $this->_reviewsCount = $this->instances()->get()->reduce( function($acc, $inst) {
        return $acc + $inst->reviews()->published()->count();
      });
    }

    return $this->_reviewsCount;
  }

  public function getNiceSemesterAttribute() {
    $sem_nb = intval(substr($this->pivot->semester, 2));

    if (substr($this->pivot->semester, 0, 2) == "BA" && $sem_nb > 2) {
      $sem_nb -= 2;
    }
    return "Semester ".$sem_nb;
  }

  public function updateAverages() {
    $sql  = "AVG(avg_grade) as total";
    $sql .= ", AVG(lectures_grade) as lectures";
    $sql .= ", AVG(exercises_grade) as exercises";
    $sql .= ", AVG(content_grade) as content";
    $sql .= ", AVG(difficulty) as difficulty";

    $averages = DB::table('reviews')
          ->select(DB::raw($sql))
          ->join('course_instances', 'course_instances.id', '=', 'reviews.course_instance_id')
          ->whereIn('status', ['accepted', 'published'])
          ->where('course_instances.course_id', $this->id)->first();

    $this->avg_overall_grade = $averages->total;
    $this->avg_lectures_grade = $averages->lectures;
    $this->avg_exercises_grade = $averages->exercises;
    $this->avg_content_grade = $averages->content;
    $this->avg_difficulty = $averages->difficulty;

    $this->save();
  }
}
