@extends('main')

@section('scripts')
{{ HTML::script('js/search.js') }}
@stop

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
		<h1>Search results for '<i>{{{ Input::get('q') }}}</i>'</h1>

		<section class="row">
		<h2><a href="#advancedFilters" data-toggle="collapse">Advanced filters</a></h2>

		{{--
			The 'advanced filters' panel is expanded only if a filter has been applied
		--}}
		<div class="well well-lg collapse {{{ $was_filtered ? 'in' : '' }}}" id="advancedFilters">
			<form id="filters-form" action="{{{ Request::URL() }}}" method="GET">
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
			  	<legend>Filter by section</legend>
		  	  	<div class="row">
		  	  		@if(Tequila::isLoggedIn())
		  	  			<a href="#" class="sections-check-mine">check mine</a> |
		  	  		@endif
		  			<a href="#" class="sections-check-all">check all</a> |
		  			<a href="#" class="sections-uncheck-all">uncheck all</a>
		  	  	</div>
			  	<div class="row">
			  		<input type="hidden" name="sections" id="sections-filter-list"
			  			value="{{{ $joined_selected_sections }}}"
			  		/>
			  		@foreach($sections as $i => $section)
					<label class="checkbox-inline col-lg-3 col-md-3" style="margin-left:0px;">
		  		  	    <input type="checkbox" value="true" data-section-id="{{{ $section->id }}}" class="section-filter"
		  		  	    	@if(in_array($section->id, $selected_sections) || empty($selected_sections))
		  		  	    		checked
		  		  	    	@endif

		  		  	    	@if($section->id == $student_section_id)
								data-is-student-section="1"
		  		  	    	@endif
		  		  	    	>
							{{{ $section->name }}}
			  	  	</label>
			  	  	@if($i > 0 && $i % 5 == 0)
						<br />
			  	  	@endif
		  	  	@endforeach
		  	  	</div>
		  		</fieldset>
	  		  <div class="row" style="margin-top: 20px;">
	  		  	<button type="submit" class="btn btn-default">Apply</button>
	  		  </div>
	  		</form>
	  	</div>
		</section>

		@include('global.course_list', [
			'courses' => $courses,
			'paginator' => $paginator,
			'pagination_links_appendings' => Input::all()
		])

		@if(sizeof($courses) == 0)
			<h2>No results</h2>
		@endif

      </div>
    </div>
  </section>
</div>

@stop