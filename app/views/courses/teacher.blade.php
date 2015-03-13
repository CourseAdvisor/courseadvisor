@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>{{{ $teacher->fullname }}}</h1>
        <p>Maybe print some stats here</p>

        <h3>{{{$teacher->firstname}}} teaches the following courses</h3>
        <div class="list-group">
          @foreach($courses as $course)
          <?php $reviewsCount = $course->reviewsCount ?>
          <a href="{{{action('CourseController@show', [
                'id' => $course['id'],
                'slug' => Str::slug($course['name'])
                ])}}}" class="list-group-item">

            <!-- desktop only -->
            <div class="pull-right hidden-xs">
              @include('global.starbar', [
                'grade' => $course->avg_overall_grade,
                'disabled' => $reviewsCount == 0,
                'comment_unsafe' => $reviewsCount.' <i class="fa fa-bookmark"></i>'
              ])
              <hr class="nomargin">
              <span class="sections pull-right">
                @foreach($course->sections as $section)
                  {{{ $section->string_id }}}-{{{ $section->pivot->semester }}}
                @endforeach
              </span>
            </div>

            <!-- mobile only -->
            <div class="pull-right visible-xs">
              @include('global.starbar', [
                'grade' => $course->avg_overall_grade,
                'disabled' => $reviewsCount == 0,
                'compact' => TRUE,
              ])
            </div>

            <h2>{{{ $course['name'] }}}</h2>
            <!-- except this -->
            <h4 class="sections visible-xs">
              @foreach($course->sections as $section)
                {{{ $section->string_id }}}-{{{ $section->pivot->semester }}}
              @endforeach
            </h4>

            <span class="clearfix"></span>
          </a>
          @endforeach
        </div>
      </div>
    </div>
  </section>
</div>


@stop