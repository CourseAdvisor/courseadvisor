
@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>{{{ $section['name'] }}} courses</h1>
        <h2>Pick a semester</h2>

        <div class="list-group" id="course_list">
        @foreach($semesters as $semester)

          <a href="{{{ action('CourseController@listBySectionSemester', [
            'section_id' => $section['string_id'],
            'semester' => $semester->semester
          ]) }}}" class="list-group-item">
            <h2>{{{ $semester->semester }}}</h2>
          </a>
        @endforeach
        </div>

        <div class="list-group">
          <a href="{{{ action('CourseController@listBySectionSemester', [
            'section_id' => $section['string_id']
          ]) }}}" class="list-group-item">
            <h3>All semesters</h3>
          </a>
        </div>

      </div>
    </div>
  </section>
</div>

@stop
