@extends('main')

@section('content')

<h1>{{{ $course->name }}} course</h1>

@if($studentCount == 0) 
<p>No students are taking this course.</p>
@else
	<p>There are {{{ $studentCount }}} students taking this course.
	<ul>
	@foreach($course->students as $student) 
		<li>
			<a href="{{{ action('StudentController@show', ['id' => $student->id]) }}}">
			{{{ $student->firstname }}}
			</a>

			({{{ $student->section->name}}})
		</li>
	@endforeach
	</ul>
@endif

<p><a href="javascript:history.back();">Back</a></p>


@stop