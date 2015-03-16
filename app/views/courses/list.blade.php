
@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>{{{ $section }}} courses</h1>
        @include('global.course_list', ['courses' => $courses])

      </div>
    </div>
  </section>
</div>

@stop
