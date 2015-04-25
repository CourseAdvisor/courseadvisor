
@extends('main')

@section('content')

    <div class="container">
        {{ Breadcrumbs::render() }}
        <section class="row">
            <div class="col-xs-12">
                <div class="page">
                    <h1>My home</h1>
                    <div class="hint">Logged in as {{{ $student->fullname }}}</div>
                    <h2>My reviews</h2>
                    @if(!count($student->reviews))
                        <div class="alert alert-info">
                            You have not submitted any review yet. Take a look at the list below to see if you can help.
                        </div>
                    @else
                        <div class="list-group" id="course_list">
                            @foreach($student->reviews as $review)
                                <a href="{{{ action('CourseController@show', [
                                'id' => $review->course->id,
                                'slug' => Str::slug($review->course->name)
                                ]) }}}" class="list-group-item">



                                    <div class="pull-right">
                                        <!-- desktop only -->
                                        <div class="pull-right hidden-xs hidden-sm">
                                            @include('global.starbar', [
                                              'grade' => $review->avg_grade
                                              ])
                                        </div>
                                        <!-- mobile only -->
                                        <div class="pull-right visible-xs visible-sm">
                                            @include('global.starbar', [
                                              'grade' => $review->avg_grade,
                                              'compact' => TRUE,
                                            ])
                                        </div>
                                        <hr class="nomargin" />
                                        <div class="pull-right">
                                            {{ $review->generatePrivacyIcon() }} &nbsp; {{ $review->generateStatusIcon() }}
                                        </div>
                                    </div>



                                    <h2> {{{ $review->course->name }}} </h2>
                                    <h3> {{{ $review->title }}}</h3>

                                </a>
                            @endforeach
                        </div>
                    @endif
                    <br/>
                    <hr/>
                    <h2>Courses I may know about</h2>
                    <a class="btn btn-large btn-default" href="{{{ action("CourseController@studyCycles") }}}">Browse all courses</a>
                    <br/><br/>
                    @include('global.course_list', [
                        'courses' => $student->studyPlans()->firstOrFail()->courses()->paginate(10),
                        'paginate' => TRUE])
                </div>
            </div>
        </section>
    </div>

@stop