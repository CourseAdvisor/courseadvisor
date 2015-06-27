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
  $error = Session::get('error-comment', null);
  $commentId = isset($target_comment) ? $target_comment->id : '';
  $reviewId = isset($target_review) ? $target_review->id : '';
  if (!empty($target_review) && empty($root)) $root = $target_review;

  // This test demultiplexes the error in $errors to make sure it belongs to this form instance
  $is_my_error = $error && $error['parent'] == $commentId && $error['root'] == $root->id
      && (($error['action'] == 'edit' && $is_editing) || ($error['action'] == 'create' && !$is_editing));


?>

{{ Form::open([
  'action' => ['ReviewController@' . ($is_editing ? 'update' : 'create') . 'Comment']
  ]) }}

  @if($root->student->id == StudentInfo::getId() && $root->is_anonymous)
  <span class="alert-danger">{{{ trans('courses.comment-anonymous-break-alert') }}}</span>
  @endif

  <input type="hidden" name="review_id" value="{{{ $reviewId }}}" />
  <input type="hidden" name="{{{ ($is_editing) ? 'comment_id' : 'parent_id' }}}" value="{{{ $commentId }}}" />
  <div class="form-group {{ ($is_my_error && isset($errors) && $errors->has('body')) ? 'has-error' : '' }}">
    <textarea class="form-control" rows="4" name="body">{{--
  --}}@if($is_my_error){{{
        Input::old('body')
   }}}@elseif($is_editing){{{
        $target_comment->body
      }}}{{--
  --}}@endif</textarea>
  @if ($is_my_error && isset($errors))
    {{ $errors->first('body', '<span class="help-block">:message</span>') }}
  @endif
  </div>

  <button class="btn btn-primary" type="submit">{{{ trans('courses.comment-publish-action') }}}</button>
  <a href="#" data-form-action="cancel">{{{ trans('global.cancel-action') }}}</a>
  @if($is_editing)
  <button
      formaction="{{{ action('ReviewController@deleteComment') }}}"
      onclick="return confirm('{{{ trans('courses.comment-delete-confirm') }}}');"
      class="btn btn-link"
      type="submit">
    <i class="fa fa-trash-o"></i>
  </button>
  @endif

{{ Form::close() }}
