@extends('main')

@section('content')

<section class="row">
<div class="col-xs-12">
  <div class="page">
<h1>{{{ $course->name }}}</h1>

@if($nbReviews > 0)
  @include('courses.starbar', ['grade' => $course->avg_overall_grade])
  <span class="starbar-comment"><a href="#reviews">{{ $nbReviews }} review(s)</a></span>
@else
  <span class="starbar-comment">No reviews for this course.</span>
@endif
<div class="clearfix"></div>
<dl class="dl-horizontal course-attrs">
  <dt>Difficulty</dt><dd>Todo</dd>
  <dt>Teacher</dt><dd><a href="#">{{ $course->teacher }}</a></dd>
  <dt>Sections</dt>
  <dd>
  	@foreach($course->sections as $section)
  		<a href="#">{{{ $section->string_id }}} - {{{ $section->pivot->semester }}} &nbsp;</a>
  	@endforeach
  </dd>
</dl>

<h2>Summary</h2>
<p>Analyse complexe: emploi de fonctions multivoques, équations de Cauchy-Riemann,
intégration complexe, théorème de Cauchy, formule de Cauchy, séries de Laurent, théorème des résidus.
Distributions tempérées sur la droite réelle: définition, exemples, calcul sur les distributions tempérées.</p>

@if($nbReviews > 0)
  <hr class="nomargin">
  <div class="row">
    <div class="col-xs-6">
      <h2>Distribution</h2>
    </div>
    <div class="col-xs-6">
      <h2>Rating</h2>
      <dl class="dl-horizontal">
        <dt>Teacher</dt>
        <dd>
        @include('courses.starbar', ['grade' => $course->avg_teacher_grade])
        </dd>

        <dt>Contents</dt>
        <dd>
        @include('courses.starbar', ['grade' => $course->avg_content_grade])
  	  </dd>

        <dt>Exercises</dt>
        <dd>
        @include('courses.starbar', ['grade' => $course->avg_exercises_grade])
        </dd>

      </dl>
    </div>
  </div>
@endif
</div>
</div>
</section>

<section class="row">
<div class="col-xs-12">
<div class="page">
<h2 id="reviews">
  Reviews
<a href="#my-review" class="pull-right btn btn-primary btn-large"><i class="fa fa-plus"></i> Review this course</a>
</h2>

@if($nbReviews == 0)
  <p>No review has been posted for this course yet. Review this course!</p>
@else
  @foreach($reviews as $review)
  	<div class="review">
  	  @include('courses.starbar', ['grade' => $review->avg_grade])


  	  <span class="starbar-comment">{{{ $review->title}}}</span>
  	  <div class="clearfix"></div>
  	  <div class="review-author">by
  	  	<a href="#">
  	  		{{{ $review->is_anonymous == 1 ? "Anonymous student" : $review->student->fullname }}}
  	  	</a>
  	  </div>
  	  <p>{{{ $review->comment }}}</p>
  	</div>
  	<hr>
  @endforeach
@endif
{{ $reviews->fragment('reviews')->links()}}
</div>
</div>
</section>

<section class="row">
<div class="col-lg-12">
<div class="page">
<h2 id="my-review">Your Review</h2>

@if(!$isLoggedIn)
  <div class="alert alert-danger" role="alert">
    You need to be {{ link_to_action('AuthController@login', 'logged in', ['next' => Request::url()]) }} to post a review.
  </div>
@elseif($hasAlreadyReviewed)
  <div class="alert alert-warning" role="alert">
    You already posted a review for this course! You can (todo) edit it.
  </div>
@else
  @if($errors->any())
    <div class="alert alert-danger" role="alert">
      <p>Some errors happened.</p>
      <ul>
      @foreach($errors->all() as $message)
        <li>{{ $message }}</li>
      @endforeach
    </div>
  @endif
{{ Form::open([
	'class' => 'row form-horizontal',
	'action' => ['CourseController@createReview', $slug, $course->id]
	]) }}

  <div class="col-md-8">
    <p>Take a couple of minutes to give your opinion on this course.</p>
      <div class="form-group">
        <input type="text" class="form-control" name="title" placeholder="Overall impression" value="{{ Input::old('title') }}">
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Difficulty</label>
        <div class="col-sm-10">
          <label class="radio-inline">
            <input type="radio" name="difficulty" id="difficulty-1" value="1" {{ Input::old('difficulty') == 1 ? 'checked' : ''}} > Free
          </label>
          <label class="radio-inline">
            <input type="radio" name="difficulty" id="difficulty-2" value="2" {{ Input::old('difficulty') == 2 ? 'checked' : ''}}> Easy
          </label>
          <label class="radio-inline">
            <input type="radio" name="difficulty" id="difficulty-3" value="3" {{ Input::old('difficulty') == 3 ? 'checked' : ''}}> Fair
          </label>
          <label class="radio-inline">
            <input type="radio" name="difficulty" id="difficulty-4" value="4" {{ Input::old('difficulty') == 4 ? 'checked' : ''}}> Difficult
          </label>
          <label class="radio-inline">
            <input type="radio" name="difficulty" id="difficulty-5" value="5" {{ Input::old('difficulty') == 5 ? 'checked' : ''}}> Extreme
          </label>
          &nbsp;&nbsp;&nbsp;
          <label class="radio-inline">
            <input type="radio" name="difficulty" id="difficulty-0" value="0" {{ Input::old('difficulty') == 0 ? 'checked' : ''}}> <span class="hint">N/A</span>
          </label>
        </div>
      </div>
  </div>
  <div class="col-md-4">
    <dl class="dl-horizontal">
      <dt>lectures</dt>
      <dd>
        <div class="pull-left" data-starbar="lectures_grade" data-value="{{ Input::old('lectures_grade') }}"
        data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
        data-content="{{ Config::get('content.reviews.tip_lectures_grade') }}">
      </dd>
      <dt>exercises</dt>
      <dd>
        <div class="pull-left" data-starbar="exercises_grade"  data-value="{{ Input::old('exercises_grade') }}"
        data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
        data-content="{{ Config::get('content.reviews.tip_exercises_grade') }}">
      </dd>
      <dt>content</dt>
      <dd>
        <div class="pull-left" data-starbar="content_grade"  data-value="{{ Input::old('content_grade') }}"
        data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
        data-content="{{ Config::get('content.reviews.tip_content_grade') }}">
      </dd>
    </dl>
  </div>
  <div class="col-sm-10">
    <div class="form-group">
      <textarea class="form-control" rows="3" name="comment" placeholder="Was this course useful to you? Did you find it interesting? Express your own opinion here without thinking about how others feel about this course.">{{ Input::old('comment') }}</textarea>
    </div>
  </div>
  <div class="col-sm-12">
    <div class="form-group">
      <div class="checkbox">
        <label>
          <input type="checkbox" name="anonymous" value="true" {{ Input::old('anonymous') ? 'checked' : '' }}> Post anonymously
          <i class="fa fa-question-circle" data-html="true" data-trigger="click" data-toggle="popover" data-placement="right"
          data-content='Your name will not be displayed in your review if you choose this option.<br />
          {{ link_to_action("StaticController@faq", "Read more.") }}'></i>
        </label>


      </div>
    </div>
  </div>
  <div class="col-sm-12">
    <input type="submit" class="btn btn-primary center-block" value="Submit my review">
  </div>
  <div class="col-sm-12">
    <p class="hint">Make sure that you understand and agree with our <a href="#">review policy</a> before submitting your review.</p>
  </div>
{{ Form::close() }}
@endif

</div>
</div>


@stop