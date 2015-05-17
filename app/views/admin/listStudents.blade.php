@extends('main')

@section('content')

<div class="container">
	{{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
		<h1 class="text-center">Registred students</h1>

		<p>There are {{{ $students->count() }}} registred students.</p>

		<div class="row">
			<div class="col-lg-8 col-lg-offset-2 col-md-8">
				<p class="pull-left"><img src="data:image/png;base64,{{ $repartitionSectionGraphData }}" /></p>
				<p><img src="data:image/png;base64,{{ $repartitionNbReviewsGraphData }}" /></p>
			</div>
		</div>

		<p>
		<ul>
		@foreach($students as $student)
			<li><b>{{ $student->fullname }}</b> ({{ $student->section->name }})
			@if ($student->reviews->count() > 0)
			- {{{ $student->reviews->count() }}} reviews posted
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