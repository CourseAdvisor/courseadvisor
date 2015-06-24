<?php
  $is_editing = isset($edit) && $edit;
?>

{{ Form::open([
  'class' => '',
  'action' => ['ReviewController@' . ($is_editing ? 'update' : 'create') . 'Comment'],
  'id' => isset($id) ? $id : ''
  ]) }}


  <input type="hidden" name="review_id" value="{{{ isset($target_review) ? $target_review : '' }}}" />
  <input type="hidden" name="comment_id" value="{{{ isset($target_comment) ? $target_comment : '' }}}" />
  <div class="form-group">
    <textarea class="form-control" name="body"></textarea>
  </div>

  <button class="btn btn-primary" type="submit">Send</button>
  <a href="#" data-form-action="cancel">cancel</a>

{{ Form::close() }}