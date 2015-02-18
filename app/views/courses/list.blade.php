
@extends('main')

@section('content')
<h1>Courses list</h1>

@foreach($courses as $course)
<h3>{{{ $course->name }}}</h3>

<p>This course is given in :
	@foreach($course->sections as $section)
		{{{ $section->name . " (".$section->pivot->semester.")" }}}, 
	@endforeach
</p>
@endforeach
@stop