@extends('main')

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
        <dt>Difficulty</dt><dd>Todo</dd>
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
            {{-- TODO --}}
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
            	  <p>{{{ $review->comment }}}</p>
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

          @if($errors->any())
            <div class="alert alert-danger" role="alert">
              <p>Some errors happened.</p>
              <ul>
              @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
              @endforeach
            </div>
          @endif

          {{ Form::open([
            'class' => 'row form-horizontal',
            'action' => ['CourseController@createReview', $slug, $course->id]
            ]) }}

          <div class="col-md-8">
            <p>Take a couple of minutes to give your opinion on this course.</p>
            <div class="form-group">
              <input type="text" class="form-control" name="title" placeholder="Overall impression" value="{{ Input::old('title') }}">
            </div>

            {{-- mobile friendly difficulty picker --}}
            <div class="form-group visible-xs">
              <label for="difficulty_mobile" class="control-label">Difficulty</label>
              <select id="difficulty-mobile" name="difficulty_mobile" class="form-control">
                <option value="1" {{ Input::old('difficulty') == 1 ? 'selected' : ''}}>free</option>
                <option value="2" {{ Input::old('difficulty') == 2 ? 'selected' : ''}}>easy</option>
                <option value="3" {{ Input::old('difficulty') == 3 ? 'selected' : ''}}>fair</option>
                <option value="4" {{ Input::old('difficulty') == 4 ? 'selected' : ''}}>hard</option>
                <option value="5" {{ Input::old('difficulty') == 5 ? 'selected' : ''}}>extreme</option>
                <option value="0" {{ Input::old('difficulty') == 0 ? 'selected' : ''}}>N/A</option>
              </select>
            </div>

            {{-- desktop difficulty picker --}}
            <div class="form-group hidden-xs">
              <label class="col-sm-2 control-label">Difficulty</label>
              <div class="col-sm-10">
                <label class="radio-inline">
                  <input type="radio" name="difficulty" id="difficulty-1" value="1" {{ Input::old('difficulty') == 1 ? 'checked' : ''}} > Free
                </label>
                <label class="radio-inline">
                  <input type="radio" name="difficulty" id="difficulty-2" value="2" {{ Input::old('difficulty') == 2 ? 'checked' : ''}}> Easy
                </label>
                <label class="radio-inline">
                  <input type="radio" name="difficulty" id="difficulty-3" value="3" {{ Input::old('difficulty') == 3 ? 'checked' : ''}}> Fair
                </label>
                <label class="radio-inline">
                  <input type="radio" name="difficulty" id="difficulty-4" value="4" {{ Input::old('difficulty') == 4 ? 'checked' : ''}}> Difficult
                </label>
                <label class="radio-inline">
                  <input type="radio" name="difficulty" id="difficulty-5" value="5" {{ Input::old('difficulty') == 5 ? 'checked' : ''}}> Extreme
                </label>
                &nbsp;&nbsp;&nbsp;
                <label class="radio-inline">
                  <input type="radio" name="difficulty" id="difficulty-0" value="0" {{ Input::old('difficulty') == 0 ? 'checked' : ''}}> <span class="hint">N/A</span>
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <dl class="dl-horizontal">
              <dt>lectures</dt>
              <dd>
                <div class="pull-left" data-starbar="lectures_grade" data-value="{{ Input::old('lectures_grade') }}"
                data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
                data-content="{{{ Config::get('content.reviews.tip_lectures_grade') }}}"></div>
              </dd>
              <dt>exercises</dt>
              <dd>
                <div class="pull-left" data-starbar="exercises_grade"  data-value="{{ Input::old('exercises_grade') }}"
                data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
                data-content="{{{ Config::get('content.reviews.tip_exercises_grade') }}}"></div>
              </dd>
              <dt>content</dt>
              <dd>
                <div class="pull-left" data-starbar="content_grade"  data-value="{{ Input::old('content_grade') }}"
                data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right"
                data-content="{{{ Config::get('content.reviews.tip_content_grade') }}}"></div>
              </dd>
            </dl>
          </div>
          <div class="col-sm-10">
            <div class="form-group">
              <textarea class="form-control" rows="3" name="comment" placeholder="Was this course useful to you? Did you find it interesting? Express your own opinion here without thinking about how others feel about this course.">{{ Input::old('comment') }}</textarea>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="anonymous" value="true" {{ Input::old('anonymous') ? 'checked' : '' }}> Post anonymously
                </label>
                <span data-trigger="hover" data-toggle="popover" data-placement="right"
                  data-content="Your name will not be displayed in your review if you choose this option.">
                    <a href="{{{ action('StaticController@faq') }}}"><i class="fa fa-question-circle" ></i></a>
                </span>
              </div>
            </div>
          </div>
          <div class="col-sm-12">
            <input type="submit" class="btn btn-primary center-block" value="Submit my review">
          </div>
          <div class="col-sm-12">
            <p class="hint">Make sure that you understand and agree with our <a href="#">review policy</a> before submitting your review.</p>
          </div>
          {{ Form::close() }}
        @endif
      </div> {{-- page --}}
    </div>
  </section>
</div>

@stop
