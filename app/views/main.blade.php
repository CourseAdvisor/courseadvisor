<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">

    <title>
    @section('page_title')
    {{{ isset($page_title) ? $page_title.' | ' : '' }}} Course Advisor
    @show
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    {{ HTML::style("css/courseadvisor.css") }}
    {{ HTML::style("css/font-awesome.min.css") }}
  </head>
  <body>
    <div class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a href="/" class="navbar-brand"><span class="logo-course">Course</span>Advisor</a>
          <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="navbar-collapse collapse" id="navbar-main">
          <ul class="nav navbar-nav main-nav">
            <li {{ MenuHelper::active_if_home() }}>
              <a href="/">Home</a>
            </li>
            <li class="sep"></li>
            <li {{ MenuHelper::active_if_controller("course") }}>
              <a href="{{{ action('CourseController@sections') }}}">
                Courses
              </a>
            </li>
            <li class="sep"></li>
            <li><a href="./about.html">About</a></li>
          </ul>

          <ul class="nav navbar-nav navbar-right">
            @if(Tequila::isLoggedIn())
              <li><a href="#">{{{ Tequila::get('firstname') }}}</a></li>
              <li><a href="{{{ action('AuthController@logout', ['next' => Request::url()]) }}}">Log out</a></li>
            @else
              <li><a href="{{{ action('AuthController@login', ['next' => Request::url()]) }}}">Log in</a></li>
            @endif
          </ul>

        </div>
      </div>
    </div>

    <div class="container">
      @if(Session::has('message'))
      <section class="row">
        <div class="col-sm-12">
          <div class="alert alert-{{{ Session::get('message')[0] }}}" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            {{{ Session::get('message')[1] }}}
          </div>
        </div>
      </section>
      @endif
    </div>

    @yield('content')


    @include('footer')


    {{ HTML::script("https://code.jquery.com/jquery-1.10.2.min.js") }}
    {{ HTML::script("js/vendor/bootstrap.min.js") }}
    {{ HTML::script("js/starbar.js") }}

    <script>
      // Initialize starbars
      $('[data-starbar]').each(function(el){
        var el = $(this);
        var starbar = new StarBar(el, {inputName: el.attr('data-starbar')});
        var initialValue = el.attr('data-value');
        if(typeof initialValue !== 'undefined') {
          starbar.setValue(initialValue);
        }
      });

      // Initialize popovers
      var popovers = $("[data-toggle=popover]");
      popovers.popover();
    </script>

    @yield('scripts')

  </body>
</html>
