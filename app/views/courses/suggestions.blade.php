@extends('main')

@section('content')
<h1>Suggestions</h1>

<p>Here are some courses you might want to leave a comment about! (Courses given to your section, lower semesters)</p>

<ul>
@foreach($courses as $course)
	<li>{{{ $course->name }}} (taught by {{{ $course->teacher }}})</li>
@endforeach
</ul>

@stop