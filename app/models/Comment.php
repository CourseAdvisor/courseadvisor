<?php

class Comment extends Commentable {
  protected $table = 'comments';

  protected $fillable = ['comment_id', 'body'];


  // Relations

  public function student() {
    return $this->belongsTo('Student');
  }

  public function parent() {
    return $this->belongsTo('Comment', 'comment_id');
  }

  public function review() {
    return $this->belongsTo('Review', 'review_id');
  }


  // Validation

  public static function rules() {
    return [
      'body' => 'max:2048',
      'review_id' => 'required'
    ];
  }

  public static function getValidator($data) {
    return Validator::make($data, self::rules());
  }

}