
@extends('main')

@section('content')

    <div class="container">
        {{ Breadcrumbs::render() }}
        <section class="row">
            <div class="col-xs-12">
                <div class="page">
                    <h1>{{{ ucfirst($cycle) }}} courses</h1>
                    <div class="list-group" id="course_list">
                        @foreach($plans as $plan)

                            <a href="{{{ action('CourseController@studyPlanCourses', [
                                'cycle' => $cycle,
                                'plan_slug' => $plan['slug']
                              ]) }}}" class="list-group-item">
                                <h2>{{{ $plan['name'] }}}</h2>
                            </a>
                        @endforeach
                    </div>

                </div>
            </div>
        </section>
    </div>

@stop
