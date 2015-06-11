@extends('main')

@section('content')

<div class="container">
  {{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1><i class="fa fa-institution"></i> Administration</h1>

        <h2>Anonymous Reviews</h2>
        <dl class="dl-horizontal">
          <dt>Accepted</dt>
          <dd><span class="label label-success" >{{{ $nbAccepted }}}</span></dd>
          <dt>Rejected</dt>
          <dd><span class="label label-danger" >{{{ $nbRejected }}}</span></dd>
        </dl>
        <a class="btn btn-primary" href="{{{action('AdminController@moderate')}}}">
          <i class="fa fa-thumbs-o-up"></i> Moderate
          @if($nbWaiting > 0)
            <span class="badge">{{{ $nbWaiting }}}</span>
          @endif
        </a>
      </div>
    </div>
  </section>
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h1><i class="fa fa-line-chart"></i> Statistics</h1>
        <div class="row">

          <div class="col-lg-3 col-sm-5 col-sm-offset-0 col-xs-12">
            <h2>Reviews ({{{ $stats['nb_reviews'] }}})</h2>
            <div class="img-awesome">
              <a href="#" data-theater="fullscreen">
                <img src="data:image/png;base64,{{ $reviewsGraph }}" class="pull-right" />
              </a>
            </div>
            <a class="btn btn-default" href="{{{action('AdminController@listReviews')}}}">
              View all
            </a>
          </div>
          <div class="col-lg-3 col-sm-offset-1 col-sm-5 col-xs-offset-0 col-xs-12">
            <h2>Students ({{{ $stats['nb_students'] }}})</h2>
            <div class="img-awesome">
              <a href="#" data-theater="fullscreen">
                <img src="data:image/png;base64,{{ $studentsGraph }}" class="pull-left" />
              </a>
            </div>
            <a class="btn btn-default" href="{{{ action('AdminController@listStudents') }}}">
              View all
            </a>
          </div>

          <div class="col-lg-3 col-lg-offset-1 col-sm-5 col-sm-offset-0 col-xs-12">
            <h2>Courses ({{{ $nbCourses }}})</h2>
            <div class="img-awesome">
              <a href="#" data-theater="fullscreen">
                <img src="data:image/png;base64,{{ $coursesGraph }}" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

@stop