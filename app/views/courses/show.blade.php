@extends('main')

@section('scripts')
{{ HTML::script('js/show-course.js') }}
@if (Config::get('app.debug'))
  {{ HTML::script('js/fill-random-review.js') }}
@endif
@stop

@section('content')

<div class="container">
  {{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
      <h1>{{{ $course->name }}}</h1>

      @if($nbReviews > 0)
        @include('global.starbar', [
          'grade' => $course->avg_overall_grade,
          'comment_unsafe' => '<a href="#reviews">'.
            Lang::choice('courses.reviews-counter', $nbReviews, ['count' => $nbReviews]).
            '</a>'
        ])
      @else
        @include('global.starbar', [
          'disabled' => TRUE,
          'comment_unsafe' => e(trans('courses.no-review-message')),
        ])
      @endif
      <div class="clearfix"></div>
      <dl class="dl-horizontal course-attrs">
        <dt>{{{ trans('courses.difficulty-label') }}}</dt><dd>
          @include('global.difficulty_bar', ['difficulty' => $course->avg_difficulty])
        </dd>
        <dt>{{{ trans('courses.teacher-label') }}}</dt><dd><a href="{{{ action('CourseController@showTeacher', [
                'id' => $course->teacher['id'],
                'slug' => Str::slug($course->teacher->fullname)
                ]) }}}">{{{ $course->teacher->fullname }}}</a></dd>
        <dt>{{{ trans('courses.studyplans-label') }}}</dt>
        <dd>
        	@foreach($course->plans as $plan)
        		<a href="{{{ action('CourseController@studyPlanCourses', [
              'cycle' => $plan->studyCycle->name,
              'plan_slug' => $plan->slug
            ])}}}">
              {{{ $plan->string_id }}}-{{{ $plan->pivot->semester }}}
            </a>
        	@endforeach
        </dd>
      </dl>
      <h2>{{{ trans('courses.summary-heading') }}}</h2>
      <p>{{ nl2br(e($course->description)) }}<br />
      <a target="_blank" href="{{{ $course->url }}}" title="coursebook page"><i class="fa fa-external-link"></i> {{{ trans('courses.read-more-action') }}}</a></p>

      @if($nbReviews > 0)
        <hr class="nomargin">
        <div class="row">
          <div class="col-xs-11 col-xs-offset-1 col-sm-6 col-sm-offset-0">
            <h2>{{{ trans('courses.distribution-heading') }}}</h2>
            <dl class="course-stats dl-horizontal">
              <dt>{{{ trans('courses.grading-5-label') }}}</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[4]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[4]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[4]['total']}}} votes</div>
              </dd>

              <dt>{{{ trans('courses.grading-4-label') }}}</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[3]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[3]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[3]['total'] }}} votes</div>
              </dd>

              <dt>{{{ trans('courses.grading-3-label') }}}</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[2]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[2]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[2]['total']}}} votes</div>
              </dd>

              <dt>{{{ trans('courses.grading-2-label') }}}</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[1]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[1]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[1]['total']}}} votes</div>
              </dd>

              <dt>{{{ trans('courses.grading-1-label') }}}</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[0]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[0]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[0]['total'] }}} votes</div>
              </dd>
            </dl>
            {{-- <p class="formula">s²=1.344 <span class="overline">x</span>=3.7 x̃=4 Q1=3</p> --}}
          </div>
          <div class="col-xs-11 col-xs-offset-1 col-sm-6 col-sm-offset-0">
            <h2>{{{ trans('courses.rating-heading') }}}</h2>
            <dl class="dl-horizontal">
              <dt>{{{ trans('courses.grading-lectures-label') }}}</dt>
              <dd>
                @include('global.starbar', [
                'grade' => $course->avg_lectures_grade,
                'disabled' => $course->avg_lectures_grade == 0
                ])
              </dd>
              <dt>{{{ trans('courses.grading-exercises-label') }}}</dt>
              <dd>
                @include('global.starbar', [
                'grade' => $course->avg_exercises_grade,
                'disabled' => $course->avg_exercises_grade == 0
                ])
              </dd>
              <dt>{{{ trans('courses.grading-content-label') }}}</dt>
              <dd>
                @include('global.starbar', [
                'grade' => $course->avg_content_grade,
                'disabled' => $course->avg_content_grade == 0
                ])
              </dd>
            </dl>
          </div>
        </div>
        @endif
      </div> {{-- page --}}
    </div>
  </section>

  <section class="row">
    <div class="col-xs-12">
      <div class="page">
        <h2 id="reviews">
          {{{ trans('courses.reviews-heading') }}}
        @if($nbReviews > 0)
          <a href="#my-review" class="pull-right btn btn-primary btn-large"><i class="fa fa-plus"></i> {{{ trans('courses.review-this-action') }}}</a>
        @endif
        </h2>

        @if($nbReviews == 0)
          <p>{{{ trans('courses.no-reviews-message') }}}</p>
        @else
          <div class="reviews">
            @for($i = 0 ; $i < count($reviews) ; $i++)
              <?php $review = $reviews[$i]; ?>
              @if($i != 0) <hr> @endif

            	<div class="review">
                @if (Tequila::isLoggedIn() && StudentInfo::getSciper() == $review->student->sciper)
                <span class="pull-right actions">
                  <a href="#"
                    data-review-id="{{{ $review->id }}}"
                    data-review-lectures-grade="{{{ $review->lectures_grade }}}"
                    data-review-exercises-grade="{{{ $review->exercises_grade }}}"
                    data-review-content-grade="{{{ $review->content_grade }}}"
                    data-review-title="{{{ $review->title }}}"
                    data-review-difficulty="{{{ $review->difficulty }}}"
                    data-review-anonymous="{{{ $review->is_anonymous ? 1 : 0 }}}"
                    class="edit-review" title="{{{ trans('courses.edit-reviews-action') }}}">
                    <i class="fa fa-pencil"></i>

                  <a href="{{{ action("CourseController@deleteReview", [
                    'reviewId'=> $review->id,
                    'courseId'=> $course->id,
                    'slug'    => $slug
                    ]) }}}" onclick="return confirm('{{{ trans('courses.delete-reviews-confirm') }}}');">
                    <i class="fa fa-trash-o"></i>
                  </a>
                  </a>
                @endif
                </span>

            	  @include('global.starbar', [
                'grade' => $review->avg_grade,
                'comment_unsafe' => htmlspecialchars($review->title)
                ])

            	  <div class="clearfix"></div>
            	  <div class="review-author">
                  @if($review->is_anonymous)
                    {{{ trans('courses.review-anonymous-author', ['section' => $review->student->section->name])}}}
                  @else
                  {{
                    trans('courses.review-author', [
                      'author' => '<a target="_blank" href="http://people.epfl.ch/'.e($review->student->sciper).'">'.e($review->student->fullname).'</a>',
                      'section' => '<span class="hint">'.$review->student->section->name.'</span>'
                      ])
                  }}
                  @endif
            	  </div>
            	  <p class="review-content">{{ nl2br(e($review->comment)) }}</p>
            	</div>
            @endfor
          </div>
        @endif
        {{ $reviews->fragment('reviews')->links()}}
      </div> {{-- page --}}
    </div>
  </section>

  <section class="row">
    <div class="col-lg-12">
      <div class="page">
        <h2 id="my-review">{{{ trans('courses.create-review-heading') }}}</h2>

        @if(!Tequila::isLoggedIn())
          <div class="alert alert-info" role="alert">
            {{ trans('courses.login-to-post-prompt', [
            'link-begin' => '<a href="'.action('AuthController@login', ['next' => Request::url()]).'">',
            'link-end' => '</a>'
            ]) }}
          </div>
        @elseif($hasAlreadyReviewed)
          <div class="alert alert-info" role="alert">
            <div class="review">
              <p class="review-content hidden">{{{ $studentReview->comment }}}</p>
              <p>
                {{ trans($studentReview->status == 'waiting' ? 'courses.review-moderation-pending-message' : 'courses.already-reviewed-message',
                  [
                  'link-begin' => '<a href="#"
                    data-review-id="'.$studentReview->id.'"
                    data-review-lectures-grade="'.$studentReview->lectures_grade.'"
                    data-review-exercises-grade="'.$studentReview->exercises_grade.'"
                    data-review-content-grade="'.$studentReview->content_grade.'"
                    data-review-title="'.htmlspecialchars($studentReview->title).'"
                    data-review-difficulty="'.$studentReview->difficulty.'"
                    data-review-anonymous="'.$studentReview->is_anonymous.'"
                    class="edit-review" title="courses.edit-reviews-action">',
                  'link-end' => '</a>'
                  ]) }}
              </p>
            </div>
          </div>
        @else

        @include('forms.create-review', [
          'data' => Input::old(),
          'errors' => $errors,
          'id' => 'create-review-form'
        ])

        @endif
      </div> {{-- page --}}
    </div>
  </section>
</div>

<div class="modal fade bs-example-modal-lg" id="edit-review-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" ><span>&times;</span></button>
        <h4 class="modal-title">{{{ trans('courses.edit-review-heading') }}}</h4>
      </div>
      <div class="modal-body">
      @include('forms.create-review', [
        'edit' => true,
        'data' => Input::old(),
        'errors' => $errors,
        'id' => 'edit-review-form'
      ])
      </div>
    </div>
  </div>
</div>

@stop
