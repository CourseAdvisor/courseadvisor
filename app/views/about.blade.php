
@extends('main')

@section('content')

<div class="container">
  {{ Breadcrumbs::render() }}

  <section class="row">
    <div class="col-xs-12">
      <div class="page">
    <h1>{{{ trans('about.heading') }}}</h1>

    {{ trans('about.authors') }}

    <h2>{{{ trans('about.policy-heading') }}}</h2>

    {{ trans('about.policy') }}
      </div>
    </div>
  </section>
</div>

@stop