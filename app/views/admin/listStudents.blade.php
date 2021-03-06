@extends('main')

@section('content')

<div class="container">
  {{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
    <h1 class="text-center">Registred students</h1>

    <p>There are {{{ $students->count() }}} registred students.</p>

    <p>
    <ul>
    @foreach($students as $student)
      <li><b>{{ $student->fullname }}</b> ({{ $student->section->name }})
      @if ($student->reviews->count() > 0)
        -
        <a href="{{{ action('AdminController@listReviews') }}}?sciper={{{ $student->sciper }}}">
          {{{ $student->reviews->count() }}} reviews posted
        </a>
      @endif

      </li>
    @endforeach
    </ul>
    </p>
      </div>
    </div>
  </section>
</div>

@stop