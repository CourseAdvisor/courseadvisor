<?php

class Comment extends Commentable {
  protected $table = 'comments';

  protected $fillable = ['review_id', 'comment_id', 'body'];


  // Relations

  public function student() {
    return $this->belongsTo('Student');
  }

  public function parent() {
    return $this->belongsTo('Comment');
  }

  public function review() {
    return $this->belongsTo('Review');
  }


  // Validation

  public static function rules() {
    return [
      'body' => 'max:2048'
    ];
  }

  public static function getValidator($data) {
    return Validator::make($data, self::rules());
  }

}