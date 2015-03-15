@extends('main')

@section('content')

<div class="container">
  <section class="row">
    <div class="col-xs-12">
      <div class="page">
		<h1>Search results for '<i>{{{ Input::get('q') }}}'</i></h1>

		<section class="row">
		<h3>Filter</h3>

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
		  <div class="form-group">
		  	<a href="#collapseFilterSection" data-toggle="collapse">Filter by section</a>

		  	<div class="container collapse {{{ !empty($sectionIds) ? 'in' : '' }}} " id="collapseFilterSection">
		  		@foreach($sections as $section)
				<div class="checkbox">
	  		  	  <label>
	  		  	    <input type="checkbox" value="true" {{{ in_array($section->id, $sectionIds) || empty($sectionIds) ? 'checked' : '' }}} name="only_sections[{{{ $section->id }}}]">
						{{{ $section->name }}}
		  	  	  </label>
		  	  	</div>
		  	  	@endforeach
		  	</div>
  		  </div>
  		  <button type="submit" class="btn btn-default">Apply</button>
  		</form>
		</section>

		@include('global.course_list', [
			'courses' => $courses,
			'pagination_links_appendings' 	=> Input::all()
		])

      </div>
    </div>
  </section>
</div>

@stop