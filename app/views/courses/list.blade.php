
@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>All courses</h1>
        <p>Note: It would be better to show a "sections" list which then links to section-specific course lists.<br/>
          Logged-in users could see an additional link in the navbar-nav to a selection of courses filtered by their section/semester.</p>
        <div class="list-group">
        @foreach($courses as $course)
          <?php $reviewsCount = $course->reviewsCount ?>

          <a href="{{{ action('CourseController@show', [
            'id' => $course['id'],
            'slug' => Str::slug($course['name'])
            ]) }}}" class="list-group-item">

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

            <!-- all platforms -->
            <h2>{{{ $course['name'] }}}</h2>
            <h3>{{{ $course['teacher']->fullname }}}</h3>
            <!-- except this -->
            <h4 class="sections visible-xs">
              @foreach($course->sections as $section)
                {{{ $section->string_id }}}-{{{ $section->pivot->semester }}}
              @endforeach
            </h4>

          </a>
        @endforeach
        </div>
        <nav>
          {{ $courses->fragment('courses')->links()}}
        </nav>
      </div>
    </div>
  </section>
</div>

@stop
