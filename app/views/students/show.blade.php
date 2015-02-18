@extends('main')

@section('page_title')
@parent > {{{ $student->firstname }}}'s profile
@stop

@section('content')
<h1>{{{ $student->firstname . ' ' . $student->lastname }}}'s profile</h1>

<p>{{{ $student->firstname }}} takes the following courses.


@if(count($student->courses) == 0)
	(No courses)
@else
<ul>
@foreach($student->courses as $course)
	<li>
		<a href="{{{ action('CourseController@show', ['id' => $course->id, 'slug' => Str::slug($course->name)]) }}}">
		{{{ $course->name }}}
		</a>
	</li>
@endforeach
</ul>
@endif
</p>

<a href="javascript:history.back();">Back</a>
@stop