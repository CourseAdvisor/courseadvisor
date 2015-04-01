
@extends('main')

@section('content')

<div class="container">
  {{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>Choose your cycle</h1>

        <div class="list-group" id="course_list">
        @foreach($cycles as $cycle)

          <a href="{{{ action('CourseController@studyPlans', [
            'cycle' => $cycle['name']
          ]) }}}" class="list-group-item">
            <h2>{{{ $cycle['name'] }}}</h2>
          </a>
        @endforeach
        </div>

      </div>
    </div>
  </section>
</div>

@stop
