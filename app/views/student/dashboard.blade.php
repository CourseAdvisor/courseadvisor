
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

        <hr>

        <h2>{{{ trans('student.dashboard-plans-picker-heading') }}}</h2>

        {{ Form::open([
          'class' => 'row form-horizontal',
          'action' => [
            'CourseController@findStudyPlan'
          ]
        ]) }}


          <div class="form-group {{ $errors->has('plan') ? 'has-error' : '' }}">
            <label for="coursepicker-plan" class="control-label col-sm-2">{{{ trans('student.plans-picker-label') }}}</label>
            <div class="col-sm-7">
              <select onchange="this.form.submit()" id="coursepicker-plan" name="plan-id" class="form-control">
                @foreach($plans as $plan)
                  <option value="{{{ $plan->id }}}" {{ ($plan->id == $studyPlanId) ? 'selected' : '' }} >
                    {{{ $plan->studyCycle->name }}} &mdash; {{{ $plan->name }}}
                  </option>
                @endforeach
              </select>
              {{ $errors->first('cycle', '<span class="help-block">:message</span>') }}
            </div>
            <div class="col-sm-2">
              <button type="submit" class="btn btn-primary">{{{ trans('student.plans-picker-submit-action') }}}</button>
            </div>
          </div>


        {{ Form::close() }}

        <hr>

        <h2>{{{ trans('student.dashboard-courses-heading') }}}</h2>
        <a class="btn btn-large btn-default" href="{{{ action("CourseController@studyCycles") }}}">
          {{{ trans('student.browse-courses-action') }}}
        </a>
        <br/><br/>
        @include('components.course_list', [
          'courses' => $studentCourses,
          'paginate' => FALSE
        ])

        <hr>

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
                'slug' => $review->course->slug
                ]) }}}"
              class="list-group-item">

              <div class="pull-right">
                <!-- desktop only -->
                <div class="pull-right hidden-xs hidden-sm">
                  @include('components.starbar', [
                  'grade' => $review->avg_grade
                  ])
                </div>

                <!-- mobile only -->
                <div class="pull-right visible-xs visible-sm">
                  @include('components.starbar', [
                  'grade' => $review->avg_grade,
                  'compact' => TRUE,
                  ])
                </div>

                <hr class="nomargin" />
                <div class="pull-right">
                  {{ ReviewHelper::makePrivacyIcon($review) }} &nbsp; {{ ReviewHelper::makeStatusIcon($review) }}
                </div>
              </div>



              <h2> {{{ $review->course->name }}} </h2>
              <h3> {{{ $review->title }}}&nbsp;</h3>

            </a>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </section>
</div>

@stop
