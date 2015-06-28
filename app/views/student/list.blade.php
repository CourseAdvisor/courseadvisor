@extends('main')

@section('page_title')
@parent > List students
@stop

@section('content')
Here is a list of students.


@if (count($students) == 0)
<p>No students in the database. Sorry !</p>
@else
  <ul>
  @foreach($students as $student)
    <li><a href="{{{ action('StudentController@show', ['id' => $student->id]) }}}">{{{ $student->firstname }}}</a></li>
  @endforeach
  </ul>
@endif

@stop