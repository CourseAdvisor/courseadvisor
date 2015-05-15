
@extends('main')

@section('content')


    <div class="container">
        {{ Breadcrumbs::render() }}
        <section class="row">
            <div class="col-xs-12">
                <div class="page">
                    <h1>{{{ trans('courses.plan-courses-heading', [
                    'plan' => $plan->name,
                    'cycle' => $cycle ])
                    }}}</h1>
                    @foreach($courses as $semester => $_courses)
                        <h2>{{{ $semester }}}</h2>
                        @include('global.course_list', [
                        'courses' => $_courses,
                        'paginate' => FALSE])

                    @endforeach
                </div>
            </div>
        </section>
    </div>

@stop
