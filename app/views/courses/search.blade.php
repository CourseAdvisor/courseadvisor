@extends('main')

@section('scripts')
{{ HTML::script('js/search.js') }}
@stop

@section('content')

<div class="container">
  {{ Breadcrumbs::render() }}
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
    <h1>{{{ trans('courses.search-heading') }}}</h1>


    <form role="search" action="{{{ action('SearchController@search') }}}" method="GET">
      <div class="row">
        <div class="col-lg-7 col-lg-offset-2 col-md-7 col-sm-9 col-sm-offset-1 col-xs-9 col-xs-offset-0">
          <div class="form-group">
            <input type="text" class="form-control" placeholder="{{{ trans('courses.search-input-placeholder') }}}" name="q" value="{{{ Input::get('q') }}}">
          </div>
        </div>
        <div class="col-sm-1 col-xs-3">
          <button class="btn btn-primary" type="submit">{{{ trans('courses.search-action') }}}</button>
        </div>
      </div>
    </form>


    <section class="row">
    <h2><a href="#advancedFilters" data-toggle="collapse"><i class="fa fa-filter"></i> {{{ trans('courses.search-filters') }}}</a></h2>
    {{--
      The 'advanced filters' panel is expanded only if a filter has been applied
    --}}
    <div class="well well-lg collapse {{{ $was_filtered ? 'in' : '' }}}" id="advancedFilters">
      <form id="filters-form" action="{{{ Request::URL() }}}#advancedFilters" method="GET">
        <input type="hidden" name="q" value="{{{ Input::get('q') }}}" />
          <div class="form-group">
            <div class="checkbox">
              <label>
                <input type="checkbox" value="1" name="only_reviewed" {{{ Input::get('only_reviewed') ? 'checked' : '' }}} >
                  Show only courses that have at least one review
              </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" value="1" name="dont_match_teachers" {{{ Input::get('dont_match_teachers') ? 'checked' : '' }}} >
                  Don't include courses where the teacher's name matches
              </label>
            </div>
          </div>
          <fieldset>
            <legend>Filter by semester</legend>
            <div class="row">
              <a href="#" class="semesters-check-all">check all</a> |
              <a href="#" class="semesters-uncheck-all">uncheck all</a>
              </div>
          <div class="row checkbox-panel">
            <input type="hidden" name="semesters" id="semesters-filter-list"
                value="{{{ $joined_selected_semesters }}}"
              />
            @foreach(Config::get('content.semesters') as $semester)
            <label class="checkbox-inline col-lg-3 col-md-3" style="margin-left:0px;">
                    <input type="checkbox" value="true" data-semester="{{{ $semester }}}" class="semester-filter"
                      @if(in_array($semester, $selected_semesters) || empty($selected_semesters))
                        checked
                      @endif
                      >
                {{{ $semester }}}
                </label>
            @endforeach
          </div>
          </fieldset>
          <fieldset>
            <legend>Sorting</legend>
            <div class="row">
              <label class="col-lg-3 col-md-3">
            Sort by :
            <select class="form-control" name="sortby">
                <option value="relevance" {{{ Input::get('sortby') == 'relevance' ? 'selected' : '' }}} >Relevance</option>
                <option value="courses.name_{{{ $Locale }}}" {{{ Input::get('sortby') == 'courses.name_' . $Locale ? 'selected' : '' }}}>Course name</option>
                <option value="teachers.lastname" {{{ Input::get('sortby') == 'teachers.lastname' ? 'selected' : '' }}}>Teacher's lastname</option>
                <option value="reviewsCount" {{{ Input::get('sortby') == 'reviewsCount' ? 'selected' : '' }}}>Number of reviews</option>
              </select>
              </label>
            </div>
            <div class="row">
              <label class="col-lg-3 col-md-3">
                      <input type="checkbox" value="true" name="desc" {{{ Input::get('desc') == true ? 'checked' : '' }}} /> Reverse order
                  </label>
            </div>
          </fieldset>
          <div class="row" style="margin-top: 20px;">
            <button type="submit" class="btn btn-default">Apply</button>
          </div>
        </form>
      </div>
    </section>

    @include('components.course_list', [
      'courses' => $courses,
      'paginator' => $paginator,
      'pagination_links_appendings' => Input::all(), 
      'pagination_fragment_appending' => 'course_list'
    ])

    @if(sizeof($courses) == 0)
      <h2>{{{ trans('courses.search-no-result') }}}</h2>
    @endif

      </div>
    </div>
  </section>
</div>

@stop
