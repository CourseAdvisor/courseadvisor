<?php namespace Helpers;

class ReviewHelper {

  public static function makeStatusIcon($review) {
    switch($review->status) {
    case 'published':
    case 'accepted':
        return '<i class="fa fa-check-square-o" title="'.trans('student.review-published-tip').'"></i>';
    case 'waiting':
        return '<i class="fa fa-square-o" title="'.trans('student.review-waiting-tip').'"></i>';
    case 'rejected':
        return '<i class="fa fa-times" title="'.trans('student.review-rejected-tip').'"></i>';
    }
  }

  public static function makePrivacyIcon($review) {
    if ($review->is_anonymous) {
      return '<i class="fa fa-eye-slash" title="'.trans('student.review-anonymous-tip').'"></i>';
    } else {
      return '<i class="fa fa-eye" title="'.trans('student.review-not-anonymous-tip').'"></i>';
    }
  }
}