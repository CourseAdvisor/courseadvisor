
@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>Courses list</h1>
        @foreach($sections as $section)
          <h2>{{{ $section['name'] }}}</h2>
          @foreach($section['semesters'] as $semester => $courses)
            <b>{{{ $semester }}}.</b>
            <ul>
            @foreach($courses as $course)
              <li>
              <a href="{{{ action('CourseController@show', [
                'id' => $course['id'],
                'slug' => Str::slug($course['name'])
                ]) }}}">
                {{{ $course['name'] }}}
              </a>
              </li>
            @endforeach
            </ul>
          @endforeach
        @endforeach
       </div>
    </div>
  </section>
</div>

@stop
