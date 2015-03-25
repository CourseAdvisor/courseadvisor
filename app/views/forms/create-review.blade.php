<?php $is_editing = isset($edit) && $edit; ?>

{{ Form::open([
  'class' => 'row form-horizontal',
  'action' => ['CourseController@' . ($is_editing ? 'update' : 'create') . 'Review', $slug, $course->id],
  'id' => isset($id) ? $id : ''
  ]) }}

@if ($is_editing)
<input type="hidden" name="reviewId" />
@endif

<div class="col-md-8">

  @if(!$is_editing)
    <p>Take a couple of minutes to give your opinion on this course.</p>
  @endif
  <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
    <input type="text" class="form-control" name="title" placeholder="Overall impression" value="{{ isset($data['title']) && $data['title'] }}">
    {{ $errors->first('title', '<span class="help-block">:message</span>') }}
  </div>

  {{-- mobile friendly difficulty picker --}}
  <div class="form-group visible-xs {{ $errors->has('difficulty_mobile') ? 'has-error' : '' }}">
    <label for="difficulty_mobile" class="control-label">Difficulty</label>
    <select id="difficulty-mobile" name="difficulty_mobile" class="form-control">
      <option value="1" {{ isset($data['difficulty']) && $data['difficulty'] == 1 ? 'selected' : ''}}>free</option>
      <option value="2" {{ isset($data['difficulty']) && $data['difficulty'] == 2 ? 'selected' : ''}}>easy</option>
      <option value="3" {{ isset($data['difficulty']) && $data['difficulty'] == 3 ? 'selected' : ''}}>fair</option>
      <option value="4" {{ isset($data['difficulty']) && $data['difficulty'] == 4 ? 'selected' : ''}}>hard</option>
      <option value="5" {{ isset($data['difficulty']) && $data['difficulty'] == 5 ? 'selected' : ''}}>extreme</option>
      <option value="0" {{ isset($data['difficulty']) && $data['difficulty'] == 0 ? 'selected' : ''}}>N/A</option>
    </select>
    {{ $errors->first('difficulty_mobile', '<span class="help-block">:message</span>') }}
  </div>

  {{-- desktop difficulty picker --}}
  <div class="form-group hidden-xs {{ $errors->has('difficulty') ? 'has-error' : '' }}">
    <label class="col-sm-2 control-label">Difficulty</label>
    <div class="col-sm-10">
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-1" value="1" {{ isset($data['difficulty']) && $data['difficulty'] == 1 ? 'checked' : ''}} > Free
      </label>
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-2" value="2" {{ isset($data['difficulty']) && $data['difficulty'] == 2 ? 'checked' : ''}}> Easy
      </label>
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-3" value="3" {{ isset($data['difficulty']) && $data['difficulty'] == 3 ? 'checked' : ''}}> Fair
      </label>
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-4" value="4" {{ isset($data['difficulty']) && $data['difficulty'] == 4 ? 'checked' : ''}}> Difficult
      </label>
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-5" value="5" {{ isset($data['difficulty']) && $data['difficulty'] == 5 ? 'checked' : ''}}> Extreme
      </label>
      &nbsp;&nbsp;&nbsp;
      <label class="radio-inline">
        <input type="radio" name="difficulty" id="difficulty-0" value="0" {{ isset($data['difficulty']) && $data['difficulty'] == 0 ? 'checked' : ''}}> <span class="hint">N/A</span>
      </label>
    </div>
    {{ $errors->first('difficulty', '<span class="help-block">:message</span>') }}
  </div>
</div>
<div class="col-md-4">
  <dl class="dl-horizontal">
    <dt>lectures</dt>
    <dd>
      <div class="pull-left" data-starbar="lectures_grade" data-value="{{ isset($data['lectures_grade']) && $data['lectures_grade'] }}"
      data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
      data-content="{{{ Config::get('content.reviews.tip_lectures_grade') }}}"></div>
    </dd>
    <dt>exercises</dt>
    <dd>
      <div class="pull-left" data-starbar="exercises_grade"  data-value="{{ isset($data['exercises_grade']) && $data['exercises_grade'] }}"
      data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
      data-content="{{{ Config::get('content.reviews.tip_exercises_grade') }}}"></div>
    </dd>
    <dt>content</dt>
    <dd>
      <div class="pull-left" data-starbar="content_grade"  data-value="{{ isset($data['content_grade']) && $data['content_grade'] }}"
      data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
      data-content="{{{ Config::get('content.reviews.tip_content_grade') }}}"></div>
    </dd>
  </dl>
  @if($errors->has('lectures_grade') OR $errors->has('exercises_grade') OR $errors->has('content_grade'))
    <span class="error">Please grade all aspects of this course</span>
  @endif
</div>
<div class="col-sm-10">
  <div class="form-group {{ $errors->has('comment') ? 'has-error' : '' }}">
    <textarea class="form-control" rows="3" name="comment" placeholder="Was this course useful to you? Did you find it interesting? Express your own opinion here without thinking about how others feel about this course.">{{ isset($data['comment']) && $data['comment'] }}</textarea>
    {{ $errors->first('comment', '<span class="help-block">:message</span>') }}
  </div>
</div>
<div class="col-sm-12">
  <div class="form-group">
    <div class="checkbox">
      <label>
        <input type="checkbox" name="anonymous" id="anonymous" value="true" {{ isset($data['anonymous']) && $data['anonymous'] ? 'checked' : '' }}> Post anonymously
      </label>
      <span data-trigger="hover" data-toggle="popover" data-placement="right"
        data-content="Your name will not be displayed in your review if you choose this option.">
          <a href="{{{ action('StaticController@faq') }}}"><i class="fa fa-question-circle" ></i></a>
      </span>
      <span class="help-block warning hidden" id="anonymous-warning">If you post anonymously, your review will be moderated by an administrator before being published.</span>
    </div>
  </div>
</div>
<div class="col-sm-12">
  <input type="submit" class="btn btn-primary center-block" value="Submit my review">
</div>

@if (!$is_editing)
<div class="col-sm-12">
  <p class="hint">Make sure that you understand and agree with our <a href="#">review policy</a> before submitting your review.</p>
</div>
@endif
{{ Form::close() }}