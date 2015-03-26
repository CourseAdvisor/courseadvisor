@extends('main')

@section('scripts')
{{ HTML::script('js/show-course.js') }}
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
          'comment_unsafe' => '<a href="#reviews">'.$nbReviews.' review(s)</a>'
        ])
      @else
        @include('global.starbar', [
          'disabled' => TRUE,
          'comment_unsafe' => 'No reviews for this course.',
        ])
      @endif
      <div class="clearfix"></div>
      <dl class="dl-horizontal course-attrs">
        <dt>Difficulty</dt><dd>
          @include('global.difficulty_bar', ['difficulty' => $course->avg_difficulty])
        </dd>
        <dt>Teacher</dt><dd><a href="{{{ action('CourseController@showTeacher', [
                'id' => $course->teacher['id'],
                'slug' => Str::slug($course->teacher->fullname)
                ]) }}}">{{{ $course->teacher->fullname }}}</a></dd>
        <dt>Sections</dt>
        <dd>
        	@foreach($course->sections as $section)
        		<a href="{{{ action('CourseController@listBySectionSemester', [
              'section_id' => $section->string_id,
              'semester' => $section->pivot->semester
            ])}}}">
              {{{ $section->string_id }}}-{{{ $section->pivot->semester }}}
            </a>
        	@endforeach
        </dd>
      </dl>
      <h2>Summary</h2>
      <p>Analyse complexe: emploi de fonctions multivoques, équations de Cauchy-Riemann,
      intégration complexe, théorème de Cauchy, formule de Cauchy, séries de Laurent, théorème des résidus.
      Distributions tempérées sur la droite réelle: définition, exemples, calcul sur les distributions tempérées.</p>

      @if($nbReviews > 0)
        <hr class="nomargin">
        <div class="row">
          <div class="col-xs-6 col-xs-offset-1 col-sm-offset-0">
            <h2>Distribution</h2>
            <dl class="course-stats dl-horizontal">
              <dt>Excellent</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[4]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[4]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[4]['total']}}} votes</div>
              </dd>

              <dt>Good</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[3]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[3]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[3]['total'] }}} votes</div>
              </dd>

              <dt>Okay</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[2]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[2]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[2]['total']}}} votes</div>
              </dd>

              <dt>Bad</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[1]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[1]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[1]['total']}}} votes</div>
              </dd>

              <dt>Terrible</dt>
              <dd>
                <div class="progress pull-left">
                  <div class="progress-bar" role="progressbar" style="width: {{{ $distribution[0]['percentage']}}}%;">
                    <span class="sr-only">{{{ $distribution[0]['total']}}} votes</span>
                  </div>
                </div>
                <div class="pull-left">{{{ $distribution[0]['total'] }}} votes</div>
              </dd>
            </dl>
            <p class="formula">s²=1.344 <span class="overline">x</span>=3.7 x̃=4 Q1=3</p>
          </div>
          <div class="col-xs-5 col-sm-6">
            <h2>Rating</h2>
            <dl class="dl-horizontal">
              <dt>Lectures</dt>
              <dd>
              @include('global.starbar', ['grade' => $course->avg_lectures_grade])
              </dd>

              <dt>Contents</dt>
              <dd>
              @include('global.starbar', ['grade' => $course->avg_content_grade])
        	  </dd>

              <dt>Exercises</dt>
              <dd>
              @include('global.starbar', ['grade' => $course->avg_exercises_grade])
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
          Reviews
        @if($nbReviews > 0)
          <a href="#my-review" class="pull-right btn btn-primary btn-large"><i class="fa fa-plus"></i> Review this course</a>
        @endif
        </h2>

        @if($nbReviews == 0)
          <p>This course hasn't been reviewed yet. Maybe you can help?</p>
        @else
          <div class="reviews">
            @for($i = 0 ; $i < count($reviews) ; $i++)
              <?php $review = $reviews[$i]; ?>
              @if($i != 0) <hr> @endif

            	<div class="review">
                @if (Tequila::isLoggedIn() && StudentInfo::getSciper() == $review->student->sciper)
                <span class="pull-right">
                  <a href="#"
                    data-review-id="{{{ $review->id }}}"
                    data-review-lectures-grade="{{{ $review->lectures_grade }}}"
                    data-review-exercises-grade="{{{ $review->exercises_grade }}}"
                    data-review-content-grade="{{{ $review->content_grade }}}"
                    data-review-title="{{{ $review->title }}}"
                    data-review-difficulty="{{{ $review->difficulty }}}"
                    data-review-anonymous="{{{ $review->is_anonymous ? 1 : 0 }}}"
                    class="edit-review" title="Edit this review">
                    <i class="fa fa-pencil"></i>
                  </a>
                @endif
                </span>
            	  @include('global.starbar', [
                'grade' => $review->avg_grade,
                'comment_unsafe' => htmlspecialchars($review->title)
                ])
            	  <div class="clearfix"></div>
            	  <div class="review-author">
                  by
                  @if($review->is_anonymous)
                    <a href="#">Anonymous student</a>
                  @else
                    <a target="_blank" href="http://people.epfl.ch/{{{ $review->student->firstname.".".$review->student->lastname }}}">
                      {{{ $review->student->fullname }}}
                    </a>
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

  <div class="modal fade bs-example-modal-lg" id="edit-review-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" ><span>&times;</span></button>
          <h4 class="modal-title">Edit your review</h4>
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

  <section class="row">
    <div class="col-lg-12">
      <div class="page">
        <h2 id="my-review">Your review</h2>

        @if(!Tequila::isLoggedIn())
          <div class="alert alert-danger" role="alert">
            You need to be {{ link_to_action('AuthController@login', 'logged in', ['next' => Request::url()]) }} to post a review.
          </div>
        @elseif($hasAlreadyReviewed)
          <div class="alert alert-warning" role="alert">
            You already posted a review for this course! You can (todo) edit it.
          </div>
        @else


        @include('forms.create-review', [
          'data' => Input::all(),
          'errors' => $errors,
          'id' => 'create-review-form'
        ])

        @endif
      </div> {{-- page --}}
    </div>
  </section>
</div>

@stop
