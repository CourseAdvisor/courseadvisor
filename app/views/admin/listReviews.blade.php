@extends('main')

@section('content')

<div class="container">
	{{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
		<h1 class="text-center">See reviews</h1>

		@if ($particularStudent)
			<p>{{{ $student->fullname }}} has posted {{{ $reviews->count() }}} reviews.</p>
		@else
			<p>There are {{{ $reviews->count() }}} reviews.</p>
		@endif

		<div class="reviews">
		@foreach ($reviews as $review)
			<div class="review">
		    </span>
			  @include('global.starbar', [
		    'grade' => $review->avg_grade,
		    'comment_unsafe' => htmlspecialchars($review->title)
		    ])
			  <div class="clearfix"></div>
			  <div class="review-author">
			  {{
		        trans('courses.review-author', [
		          'author' => '<a target="_blank" href="http://people.epfl.ch/'.e($review->student->sciper).'">'.e($review->student->fullname).'</a>',
		          'section' => '<span class="hint">'.$review->student->section->name.'</span>'
		          ])
		      }}

		      @if($review->is_anonymous)
		        (anonymous)
		      @endif

			  </div>
			  <p class="review-content">{{ nl2br(e($review->comment)) }}</p>
			</div>
		@endforeach
		</div>
      </div>
    </div>
  </section>
</div>

@stop