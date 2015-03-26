@extends('main')
@section('content')

<section id="splash">
  <div class="overlay">
    <div class="container">
      <h1>
        Make the <strong>right</strong> decision
      </h1>
      <p>
        On CourseAdvisor you benefit directly from past students feedbackto find the courses that are right for you.<br>
        <br>
        Lookup a course and find out what past students thought about it.
      </p>
      <form action="{{{ action('SearchController@search') }}}" method="GET" id="searchForm">
      <div class="hero-search">
        <div class="input-group input-group-lg">
          <input type="text" class="form-control" name="q" placeholder="Search a course by title, field, teacher, ...">
          <span class="input-group-btn">
            <button class="btn btn-primary" type="button" onclick="document.getElementById('searchForm').submit();">Go!</button>
          </span>
        </div>
      </div>
      </form>
    </div>
  </div>
</section>

@stop
