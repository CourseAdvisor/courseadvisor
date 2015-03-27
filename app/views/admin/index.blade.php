@extends('main')

@section('content')

<div class="container">
	{{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
		<h1 class="text-center">Administration</h1>

		<div class="row">
			<p>Hello, {{{ Tequila::get('firstname') }}}! What do you want to do?</p>
		</div>

		<div class="row">
			<div class="admin-panel well col-lg-6">
				<span class="pull-right"><i class="fa fa-comments fa-2x"></i></span>
				<h3>{{ link_to_action('AdminController@moderate', 'Moderate reviews' )}}</h3>
				<ul>
					<li><strong>{{{ $nbWaiting }}}</strong> pending</li>
					<li><strong>{{{ $nbAccepted }}}</strong> accepted</li>
					<li><strong>{{{ $nbRejected }}}</strong> rejected</li>
				</ul>
			</div>

			<div class="admin-panel well col-lg-5 col-lg-offset-1">
				<span class="pull-right"><i class="fa fa-bar-chart fa-2x"></i></span>
				<h3>Statistics</h3>
				<ul>
					<li><strong>{{{ $stats['nb_courses'] }}}</strong> courses</li>
					<li><strong>{{{ $stats['nb_reviews'] }}}</strong> reviews</li>
					<li><strong>{{{ $stats['nb_students'] }}}</strong> registred students</li>
				</ul>
			</div>
		</div>
      </div>
    </div>
  </section>
</div>

@stop