@extends('main')

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
			<form action="{{{ Request::URL() }}}" method="GET">
			  <input type="hidden" name="q" value="{{{ Input::get('q') }}}" />
	  		  <div class="form-group">
	  		    <div class="checkbox">
	  		  	  <label>
	  		  	    <input type="checkbox" value="true" name="only_reviewed" {{{ Input::get('only_reviewed') ? 'checked' : '' }}} >
	  		  	      Show only courses that have at least one review
		  	  	  </label>
		  	  	</div>
	  		  </div>
			  <fieldset>
			  	<legend>Filter by section</legend>
		  		@foreach($sections as $section)
				<div class="checkbox">
	  		  	  <label>
	  		  	    <input type="checkbox" value="true" {{{ in_array($section->id, $sectionIds) || empty($sectionIds) ? 'checked' : '' }}} name="sections[{{{ $section->id }}}]">
						{{{ $section->name }}}
		  	  	  </label>
		  	  	</div>
		  	  	@endforeach
		  		</fieldset>
	  		  <button type="submit" class="btn btn-default">Apply</button>
	  		</form>
	  	</div>
		</section>

		@include('global.course_list', [
			'courses' => $courses,
			'pagination_links_appendings' 	=> Input::all()
		])

		@if(sizeof($courses) == 0)
			<h2>No results</h2>
		@endif

      </div>
    </div>
  </section>
</div>

@stop