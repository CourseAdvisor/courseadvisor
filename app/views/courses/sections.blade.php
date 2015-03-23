
@extends('main')

@section('content')

<div class="container">
  {{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>Sections</h1>

        <div class="list-group" id="course_list">
        @foreach($sections as $section)

          <a href="{{{ action('CourseController@sectionSemester', [
            'section_id' => $section['string_id']
          ]) }}}" class="list-group-item">
            <h3 class="pull-right">{{{ $section['string_id'] }}}</h3>
            <h2>{{{ $section['name'] }}}</h2>
          </a>
        @endforeach
        </div>

      </div>
    </div>
  </section>
</div>

@stop
