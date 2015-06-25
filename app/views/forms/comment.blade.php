{{--

  Displays a form to comment a review or a comment

  params:


  Use either:
    - target_comment: The comment to comment (reply to)
    - root: The root review for this comment thread
  or:
    - target_review: The review to comment
  or:
    - edit: true
    - target_comment: The comment to edit
--}}

<?php
  $is_editing = isset($edit) && $edit;
  if (!empty($target_review) && empty($root)) $root = $target_review;
?>

{{ Form::open([
  'action' => ['ReviewController@' . ($is_editing ? 'update' : 'create') . 'Comment']
  ]) }}

  @if($root->student->id == StudentInfo::getId() && $root->is_anonymous)
  <span class="alert-danger">You are about to comment a review that you posted anonymously but comments are not anonymous. Beware not to jeopardize your review.</span>
  @endif

  <input type="hidden" name="review_id" value="{{{ isset($target_review) ? $target_review->id : '' }}}" />
  <input type="hidden" name="comment_id" value="{{{ isset($target_comment) ? $target_comment->id : '' }}}" />
  <div class="form-group">
    <textarea class="form-control" name="body">{{--
  --}}@if($is_editing){{{
        $target_comment->body
      }}}{{--
  --}}@endif</textarea>
  </div>

  <button class="btn btn-primary" type="submit">Send</button>
  <a href="#" data-form-action="cancel">cancel</a>
  <button
      formaction="{{{ action('ReviewController@deleteComment') }}}"
      onclick="return confirm('{{{ trans('courses.delete-comment-confirm') }}}');"
      class="btn btn-link"
      type="submit">
    <i class="fa fa-trash"></i>
  </button>

{{ Form::close() }}