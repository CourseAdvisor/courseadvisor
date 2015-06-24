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
}