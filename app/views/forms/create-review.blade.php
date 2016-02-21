<?php
  $is_editing = isset($edit) && $edit;
  $is_modal = isset($modal) && $modal;
?>


{{ Form::open([
  'class' => 'row form-horizontal',
  'action' => ['ReviewController@' . ($is_editing ? 'update' : 'create') . 'Review'],
  'id' => isset($id) ? $id : ''
  ]) }}

<input type="hidden" name="course_instance_id" value="{{{ $course->currentInstance->id }}}" />

@if ($is_modal)
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" ><span>&times;</span></button>
  <h3 class="modal-title">{{{ trans('courses.edit-review-heading') }}}</h3>
</div>
<div class="modal-body container-fluid">
@endif

@if ($is_editing)
<input type="hidden" name="review_id" value="{{{ isset($data['review_id']) ? $data['review_id'] : '' }}}" />
@endif

<div class="col-md-8">

  @if(!$is_editing)
    <p>{{{ trans('courses.create-review-message') }}}</p>
  @endif

    {{-- mobile friendly difficulty picker --}}
  <div class="form-group visible-xs {{ $errors->has('difficulty_mobile') ? 'has-error' : '' }}">
    <label for="difficulty_mobile" class="control-label">{{{ trans('courses.difficulty-label') }}}</label>
    <select id="difficulty-mobile" name="difficulty_mobile" class="form-control">
      <option value="1" {{ isset($data['difficulty']) && $data['difficulty'] == 1 ? 'selected' : ''}}>
        {{{ trans('courses.difficulty-1-label') }}}
      </option>
      <option value="2" {{ isset($data['difficulty']) && $data['difficulty'] == 2 ? 'selected' : ''}}>
        {{{ trans('courses.difficulty-2-label') }}}
      </option>
      <option value="3" {{ isset($data['difficulty']) && $data['difficulty'] == 3 ? 'selected' : ''}}>
        {{{ trans('courses.difficulty-3-label') }}}
      </option>
      <option value="4" {{ isset($data['difficulty']) && $data['difficulty'] == 4 ? 'selected' : ''}}>
        {{{ trans('courses.difficulty-4-label') }}}
      </option>
      <option value="5" {{ isset($data['difficulty']) && $data['difficulty'] == 5 ? 'selected' : ''}}>
        {{{ trans('courses.difficulty-5-label') }}}
      </option>
      <option value="0" {{ !isset($data['difficulty']) || $data['difficulty'] == 0 ? 'selected' : ''}}>
        {{{ trans('courses.difficulty-na-label') }}}
      </option>
    </select>
    {{ $errors->first('difficulty_mobile', '<span class="help-block">:message</span>') }}
  </div>

  {{-- desktop difficulty picker --}}
  <div class="form-group hidden-xs {{ $errors->has('difficulty') ? 'has-error' : '' }}">
    <label class="col-sm-2 control-label">{{{ trans('courses.difficulty-label') }}}</label>
    <div class="col-sm-10">
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-1" value="1"
          {{ isset($data['difficulty']) && $data['difficulty'] == 1 ? 'checked' : ''}} >
        {{{ trans('courses.difficulty-1-label') }}}
      </label>
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-2" value="2"
          {{ isset($data['difficulty']) && $data['difficulty'] == 2 ? 'checked' : ''}} >
        {{{ trans('courses.difficulty-2-label') }}}
      </label>
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-3" value="3"
          {{ isset($data['difficulty']) && $data['difficulty'] == 3 ? 'checked' : ''}} >
        {{{ trans('courses.difficulty-3-label') }}}
      </label>
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-4" value="4"
          {{ isset($data['difficulty']) && $data['difficulty'] == 4 ? 'checked' : ''}} >
        {{{ trans('courses.difficulty-4-label') }}}
      </label>
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-5" value="5"
          {{ isset($data['difficulty']) && $data['difficulty'] == 5 ? 'checked' : ''}} >
        {{{ trans('courses.difficulty-5-label') }}}
      </label>
      &nbsp;&nbsp;&nbsp;
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-0" value="0"
          {{ !isset($data['difficulty']) || $data['difficulty'] == 0 ? 'checked' : ''}}>
        <span class="hint">{{{ trans('courses.difficulty-na-label') }}}</span>
      </label>
    </div>
    {{ $errors->first('difficulty', '<span class="help-block">:message</span>') }}
  </div>

  <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
    <input type="text" class="form-control" name="title"
      placeholder="{{{ trans('courses.review-title-placeholder') }}}"
      value="{{ isset($data['title']) ? $data['title'] : '' }}">
    {{ $errors->first('title', '<span class="help-block">:message</span>') }}
  </div>
</div>
<div class="col-md-4" name="review-grades">
  <dl class="dl-horizontal">
    <dt>{{{ trans('courses.grading-lectures-label') }}}</dt>
    <dd>
      <div class="pull-left" data-starbar="lectures_grade,clearable" data-value="{{ isset($data['lectures_grade']) ? $data['lectures_grade'] : ''}}"
      data-container="body" data-toggle="popover" data-trigger="hover" data-placement="bottom"
      data-content="{{{ trans('courses.grade-lectures-helper') }}}"></div>
    </dd>
    <dt>{{{ trans('courses.grading-exercises-label') }}}</dt>
    <dd>
      <div class="pull-left" data-starbar="exercises_grade,clearable"  data-value="{{ isset($data['exercises_grade']) ? $data['exercises_grade'] : ''}}"
      data-container="body" data-toggle="popover" data-trigger="hover" data-placement="bottom"
      data-content="{{{ trans('courses.grade-exercises-helper') }}}"></div>
    </dd>
    <dt>{{{ trans('courses.grading-content-label') }}}</dt>
    <dd>
      <div class="pull-left" data-starbar="content_grade,clearable"  data-value="{{ isset($data['content_grade']) ? $data['content_grade'] : '' }}"
      data-container="body" data-toggle="popover" data-trigger="hover" data-placement="bottom"
      data-content="{{{ trans('courses.grade-content-helper') }}}"></div>
    </dd>
  </dl>
  @if($errors->has('lectures_grade') OR $errors->has('exercises_grade') OR $errors->has('content_grade'))
    <span class="error">{{{ trans('courses.incomplete-grading-message') }}}</span>
  @endif
</div>
<div class="col-sm-10">
  <div class="form-group {{ $errors->has('comment') ? 'has-error' : '' }}">
    <textarea class="form-control" rows="3" name="comment" placeholder="{{{ trans('courses.review-body-placeholder') }}}">{{ isset($data['comment']) ? $data['comment'] : '' }}</textarea>
    {{ $errors->first('comment', '<span class="help-block">:message</span>') }}
  </div>
</div>
<div class="col-sm-12">
  <div class="form-group">
    <div class="checkbox">
      <label>
        <input type="checkbox" name="anonymous" id="anonymous" value="true"
          {{ isset($data['anonymous']) && $data['anonymous'] ? 'checked' : '' }}>
          {{{ trans('courses.anonymous-post-label') }}}
      </label>
      <span data-trigger="hover" data-toggle="popover" data-placement="right"
        data-content="Your name will not be displayed in your review if you choose this option.">
          <a href="{{{ action('StaticController@about') }}}"><i class="fa fa-question-circle" ></i></a>
      </span>
      <span class="help-block warning hidden" id="anonymous-warning">
        {{{ trans('courses.anonymous-post-helper') }}}
      </span>
    </div>
  </div>
</div>

@if (!$is_modal) {{-- otherwise place actions in modal footer --}}
  <div class="col-sm-12">
    @if ($is_editing)
      <button
          formaction="{{{ action("ReviewController@deleteReview") }}}"
          data-action="delete-review"
          onclick="return confirm('{{{ trans('courses.delete-reviews-confirm') }}}');"
          class="btn btn-link pull-left"
          type="submit"
      >
        <i class="fa fa-trash-o"></i>
        {{{ trans('courses.delete-review-action') }}}
      </button>
      <input type="submit" class="btn btn-primary center-block" value="{{{ trans('courses.create-review-action') }}}">
    @else
      <input type="submit" class="btn btn-primary center-block" value="{{{ trans('courses.create-review-action') }}}">
    @endif
  </div>
@endif

@if (!$is_editing)
<div class="col-sm-12">
  {{-- TODO: <p class="hint">Make sure that you understand and agree with our <a href="#">review policy</a> before submitting your review.</p> --}}
</div>
@endif

@if ($is_modal)
</div> {{-- .modal-body --}}
<div class="modal-footer">
  @if ($is_editing)
    <button
        formaction="{{{ action("ReviewController@deleteReview") }}}"
        data-action="delete-review"
        onclick="return confirm('{{{ trans('courses.delete-reviews-confirm') }}}');"
        class="btn btn-link pull-left"
        type="submit"
    >
      <i class="fa fa-trash-o"></i>
      {{{ trans('courses.delete-review-action') }}}
    </a>
  @endif
  <button type="button" class="btn btn-default" data-dismiss="modal">
    {{{ trans('global.cancel-action') }}}
  </button>
  @if ($is_editing)
    <input type="submit" class="btn btn-primary" value="{{{ trans('courses.update-review-action') }}}">
  @else
    <input type="submit" class="btn btn-primary" value="{{{ trans('courses.create-review-action') }}}">
  @endif
</div>
@endif

{{ Form::close() }}
