@extends('main')

@section('scripts')
{{ HTML::script('js/admin/moderate.js')}}
@stop

@section('content')

<div class="container">
	{{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
		<h1 class="text-center">Moderate reviews</h1>

		@if ($reviews->count() == 0)
			<div class="row">
				<h1>No review to moderate!</h1>
			</div>
		@else
			<div class="row">
				<p><span id="reviews-count">{{{ $reviews->count() }}}</span> reviews to moderate.</p>
			</div>
		@endif

		@foreach ($reviews as $review)
			<div class="row display-table review">
				<div class="col-lg-1 display-cell">
					<a href="#" class="moderate" data-action="accept" data-review-id="{{{ $review->id }}}">
						<i class="fa fa-thumbs-up fa-2x" style="color: green;"></i>
					</a>
				</div>
				<div class="well well-lg col-lg-10 col-md-10 col-sm-10 display-cell">
					<div class="row">
						<div class="col-lg-10">
							<h3>{{{ $review->title }}}</h3>
							<p>By <i>{{{ $review->student->fullname }}} ({{{ $review->student->section->string_id }}}-{{{ $review->student->semester }}})</i>,
								for course {{ link_to_action('CourseController@show', $review->course->name, [Str::slug($review->course->name), $review->course->id])}}
								<br>
								@if ($review->student->isRegistered($review->course_id))
									<i class="fa fa-check"></i> Is registered to this course
								@else
									<i class="fa fa-exclamation-triangle"></i> Warning: student has no registration for this course
								@endif
							</p>
							<p>{{ nl2br(e($review->comment)) }}</p>
						</div>
						<div class="col-lg-2">
							<p>
							Exercises: {{{ $review->exercises_grade }}}/5<br />
							Lectures: {{{ $review->lectures_grade }}}/5<br />
							Content: {{{ $review->content_grade }}}/5<br />
							Difficulty: {{{ $review->difficulty }}}
							</p>
						</div>
					</div>
				</div>
				<div class="col-lg-1 display-cell">
					<a href="#" class="moderate" data-action="reject" data-review-id="{{{ $review->id }}}">
						<i class="fa fa-thumbs-down fa-2x" style="color: red;"></i>
					</a>
				</div>
			</div>
		@endforeach
      </div>
    </div>
  </section>
</div>

@stop