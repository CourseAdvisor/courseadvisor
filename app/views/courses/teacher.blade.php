@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>{{{ $teacher->fullname }}}</h1>
        <p><a href="{{{ $teacher->peoplePageLink }}}" target="_blank"><i class="fa fa-external-link"></i> {{{$teacher->firstname}}}'s people page</a></p>

        <h3>{{{$teacher->firstname}}} teaches the following courses</h3>
        @include('global.course_list', ['courses' => $courses])
      </div>
    </div>
  </section>
</div>


@stop
