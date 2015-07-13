@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>{{{ $teacher->fullname }}}</h1>
        <p>
            <a href="{{{ $teacher->peoplePageLink }}}" target="_blank"><i class="fa fa-external-link"></i>
                {{{ trans('courses.teacher-more-info', [
                  'teacher' => $teacher->lastname ]) }}}
            </a>
        </p>

        <h3>{{{ trans('courses.teacher-courses-heading', [
          'teacher' => $teacher->firstname ])
        }}}</h3>
        @include('components.course_list', ['courses' => $courses])
      </div>
    </div>
  </section>
</div>


@stop
