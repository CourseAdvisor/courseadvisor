
@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1>All courses</h1>
        <p>Note: It would be better to show a "sections" list which then links to section-specific course lists.<br/>
          Logged-in users could see an additional link in the navbar-nav to a selection of courses filtered by their section/semester.</p>

        @include('global.course_list', ['courses' => $courses])

      </div>
    </div>
  </section>
</div>

@stop
