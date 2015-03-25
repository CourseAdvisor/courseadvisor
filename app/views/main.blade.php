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
            {{--<li>
              <a href="#" id="search-icon"><i class="fa fa-2x fa-search"></i></a>
            </li>--}}
            @if(Tequila::isLoggedIn())
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><i class="fa fa-user fa-2x"></i></a>
               <ul class="dropdown-menu fa-ul" role="menu">
                 <li><a href="#"><i class="fa fa-fw fa-user"></i> {{{ Tequila::get('firstname') }}}</a></li>
                 <li><a href="{{{ action('AuthController@logout', ['next' => Request::url()]) }}}"><i class="fa fa-sign-out fa-fw"></i>  Log out</a></li>
               </ul>
            </li>
            @else
              <li><a href="{{{ action('AuthController@login', ['next' => Request::url()]) }}}">
                <i class="fa fa-2x fa-sign-in" style="vertical-align:middle;"></i>
                Tequila login
              </a></li>
            @endif
          </ul>


          {{--<form class="nav navbar-form navbar-right" style="display: none;" action="{{{ action('SearchController@search')}}}" method="GET" role="search" id="search-form">
            <div class="input-group">
                <input type="text" class="form-control input-sm" name="q" id="search-box" placeholder="Search courses" value="{{{ Input::get('q') }}}"/>
                <span class="input-group-addon">
                    <a class="fa fa-search" id="search-button" style="color: black;" href="#"></a>
                </span>
            </div>
          </form>--}}



          {{--<ul class="nav navbar-nav navbar-right">
            @if(Tequila::isLoggedIn())
              <li><a href="#">{{{ Tequila::get('firstname') }}}</a></li>
              <li><a href="{{{ action('AuthController@logout', ['next' => Request::url()]) }}}">Log out</a></li>
            @else
              <li><a href="{{{ action('AuthController@login', ['next' => Request::url()]) }}}">Log in</a></li>
            @endif
          </ul>--}}


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


    <footer class="footer">
      <div class="container">
        <div class="row">
          <div class="col-sm-4 col-xs-6">
            <h2><span class="logo-course">Course</span>Advisor</h2>
            <ul>
              <li><a href="{{{ action('CourseController@sections') }}}">Browse courses</a></li>
              <li><a href="{{{ action('AuthController@login') }}}">Log in</a></li>
              <li><a href="#">About</a></li>
            </ul>
          </div>
          <div class="col-sm-4 col-xs-6">
            <h2>Follow us</h2>
            <ul>
              <li><a href="#">Facebook</a></li>
              <li><a href="#">Twitter</a></li>
              <li><a href="#">Blog</a></li>
            </ul>
          </div>
          <div class="col-sm-4 hidden-xs">
            <p class="footnote">Brought to all EPFL students with love from IC.<br />
            This site is not endorsed by EPFL.</p>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-5 col-md-5 col-sm-8 col-xs-10 col-lg-offset-7 col-md-offset-7 col-sm-offset-4 col-xs-offset-2 copyright">
            Copywhatever 2015, <a href="http://christophetd.fr">christophetd</a>, <a href="http://hmil.fr">hmil</a> and <a href="http://rickrolled.fr">contributors</a>.
          </div>
        </div>
      </div>
    </footer>


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
        el.data('starbar', starbar);
      });

      // Initialize popovers
      var popovers = $("[data-toggle=popover]");
      popovers.popover();

      $('#search-icon').click(function() {
        $(this).fadeOut(function() {
          $('#search-form').fadeIn();
        });
        return false;
      });
    </script>

    @yield('scripts')

  </body>
</html>
