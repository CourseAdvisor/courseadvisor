
@extends('main')

@section('content')

<div class="container">
  {{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>{{{ trans('student.dashboard-heading') }}}</h1>
        <div class="hint">
          {{{ trans('student.logged-in-status', ['student' => $student->fullname]) }}}
        </div>

        <h2>{{{ trans('student.dashboard-reviews-heading') }}}</h2>
        @if(!count($student->reviews))
          <div class="alert alert-info">
            {{{ trans('student.no-reviews-message') }}}
          </div>
        @else
          <div class="list-group" id="course_list">
            @foreach($student->reviews as $review)
            <a href="{{{ action('CourseController@show', [
                'id' => $review->course->id,
                'slug' => Str::slug($review->course->name)
                ]) }}}"
              class="list-group-item">

              <div class="pull-right">
                <!-- desktop only -->
                <div class="pull-right hidden-xs hidden-sm">
                  @include('global.starbar', [
                  'grade' => $review->avg_grade
                  ])
                </div>

                <!-- mobile only -->
                <div class="pull-right visible-xs visible-sm">
                  @include('global.starbar', [
                  'grade' => $review->avg_grade,
                  'compact' => TRUE,
                  ])
                </div>

                <hr class="nomargin" />
                <div class="pull-right">
                  {{ $review->generatePrivacyIcon() }} &nbsp; {{ $review->generateStatusIcon() }}
                </div>
              </div>



              <h2> {{{ $review->course->name }}} </h2>
              <h3> {{{ $review->title }}}</h3>

            </a>
            @endforeach
          </div>
        @endif
        <br/>
        <hr/>

        <h2>{{{ trans('student.dashboard-courses-heading') }}}</h2>
        <a class="btn btn-large btn-default" href="{{{ action("CourseController@studyCycles") }}}">
          {{{ trans('student.browse-courses-action') }}}
        </a>
        <br/><br/>
        @include('global.course_list', [
          'courses' => $student->studyPlans()->firstOrFail()->courses()->paginate(10),
          'paginate' => TRUE])
      </div>
    </div>
  </section>
</div>

@stop