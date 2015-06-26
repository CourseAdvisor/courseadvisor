<?php

/*
  A comment is always linked to a review. Top-level comment have NULL parent.
*/
class Comment extends Commentable {
  protected $table = 'comments';
  protected $comment_key = 'parent_id';

  protected $fillable = ['parent_id', 'body'];


  // Relations

  public function student() {
    return $this->belongsTo('Student');
  }

  public function parent() {
    return $this->belongsTo('Comment', 'parent_id');
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