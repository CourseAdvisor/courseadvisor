{{--
  Displays a list of courses.

  parameters:
  - (REQUIRED) collection $courses : the courses to display
  - boolean $paginate : if the list should be paginated or not (default: yes)
  - array $pagination_links_appendings : An array to be passed to the 'appends' method.
    See http://laravel.com/docs/4.2/pagination#appending-to-pagination-links
--}}
<div class="list-group" id="course_list">
@foreach($courses as $course)
  <?php $reviewsCount = $course['reviewsCount'] ?>

  <a href="{{{ action('CourseController@show', [
    'id' => $course['id'],
    'slug' => Str::slug($course['name'])
    ]) }}}" class="list-group-item">


      <!-- desktop only -->
      <div class="pull-right hidden-xs hidden-sm">
        <div class="pull-right">
          @include('global.starbar', [
            'grade' => $course['avg_overall_grade'],
            'disabled' => $reviewsCount == 0,
            'comment_unsafe' => $reviewsCount.' <i class="fa fa-comments"></i>'
          ])
        </div>
        <hr class="nomargin">
        <span class="sections pull-right">
        @foreach($course['plans'] as $plan)
            {{{ $plan['string_id'] }}}-{{{ $plan['semester'] ? $plan['semester'] : $plan->pivot->semester }}}
        @endforeach
        </span>
      </div>

      <!-- mobile only -->
      <div class="pull-right visible-xs visible-sm">
        @include('global.starbar', [
          'grade' => $course['avg_overall_grade'],
          'disabled' => $reviewsCount == 0,
          'compact' => TRUE,
        ])
      </div>

    <!-- all platforms -->
    <h2>{{{ $course['name'] }}}</h2>
    <h3>{{{ $course['teacher']['fullname'] }}}&nbsp;</h3>
    <!-- except this -->
    <h4 class="sections visible-xs visible-sm">
      @foreach($course['plans'] as $plan)
        {{{ $plan['string_id'] }}}-{{{ $plan['semester'] ? $plan['semester'] : $plan->pivot->semester }}}
      @endforeach
    </h4>

  </a>
@endforeach
</div>

@if(!isset($paginate) || $paginate)
<nav>
  <?php $paginator = isset($paginator) ? $paginator : $courses ?>
  @if(isset($pagination_links_appendings))
    {{ $paginator->appends($pagination_links_appendings)->links() }}
  @else
    {{ $paginator->links() }}
  @endif
</nav>
@endif
