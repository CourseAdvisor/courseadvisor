<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">

    <title>
    @section('page_title')
    {{{ isset($page_title) ? $page_title.' | ' : '' }}} CourseAdvisor
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

          <a href="/" class="navbar-brand hidden-xs"><span class="logo-course">Course</span>Advisor</a>

          {{-- mobile-only search --}}
          <form class="navbar-form navbar-right visible-xs mobile-search" role="search" action="{{{ action('SearchController@search') }}}" method="GET">
            <div class="form-group">
              <input type="text" class="form-control" placeholder="Search" name="q" value="{{{ Input::get('q') }}}">
            </div>
          </form>
          <button class="navbar-search visible-xs" type="button">
            <span class="fa fa-search"></span>
          </button>

          <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="navbar-collapse collapse" id="navbar-main">

          {{-- desktop nav --}}
          <ul class="nav navbar-nav navbar-right hidden-xs">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
                <i class="flag-icon flag-icon-{{{ LaravelLocalization::getCurrentLocale() }}}" title="choose language"></i>
                <i class="fa fa-caret-down"></i>
              </a>
              <ul class="dropdown-menu locale-dropdown" role="menu">
                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                  <li>
                    <a rel="alternate" hreflang="{{$localeCode}}" href="{{LaravelLocalization::getLocalizedURL($localeCode) }}">
                      <i class="flag-icon flag-icon-{{{ $localeCode }}}" title="{{{ $properties['native'] }}}"> </i>
                    </a>
                  </li>
                @endforeach
              </ul>
            </li>
            @if(Tequila::isLoggedIn())
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
                <i class="fa fa-user">
                  @if (StudentInfo::isAdmin() && ($waitingCount = Review::waiting()->count()) > 0)
                    <sup class="text-danger">{{{ $waitingCount }}}</sup>
                  @endif
                </i>
                <i class="fa fa-caret-down"></i>
              </a>
              <ul class="dropdown-menu fa-ul" role="menu">
                @if (StudentInfo::isAdmin())
                <li><a href="{{{ action('AdminController@index') }}}"<i class="fa fa-lock"></i> Admin</a></li>
                @endif
                <li><a href="{{{ action('StudentController@dashboard') }}}"><i class="fa fa-dashboard"></i> {{{ trans('global.dashboard-action') }}}</a></li>
                <li><a href="{{{ action('AuthController@logout', ['next' => Request::root()]) }}}"><i class="fa fa-sign-out"></i> {{{ trans('global.logout-action') }}}</a></li>
              </ul>
            </li>
            @else
            <li>
              <a href="{{{ action('AuthController@login', ['next' => Request::url()]) }}}">
                {{{ trans('global.login-action') }}}
              </a>
            </li>
            @endif
          </ul>

          {{-- desktop search --}}
          <form class="navbar-form navbar-right hidden-xs desktop-search" role="search" action="{{{ action('SearchController@search') }}}" method="GET">
            <div class="input-group">
              <input type="text" class="form-control" placeholder="Search" name="q" value="{{{ Input::get('q') }}}">
              <span class="input-group-btn">
                <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
              </span>
            </div>
          </form>


          {{-- mobile nav --}}
          <ul class="nav navbar-nav main-nav visible-xs">
            @if(Tequila::isLoggedIn())
              <li><a href="{{{ action('StudentController@dashboard') }}}"><i class="fa fa-dashboard"></i> {{{ trans('global.dashboard-action') }}}</a></li>
              <li><a href="{{{ action('AuthController@logout') }}}">
                <i class="fa fa-sign-out"></i> {{{ trans('global.logout-action') }}}
              </a></li>
            @else
              <li><a href="{{{ action('AuthController@login', ['next' => Request::url()]) }}}">
                <i class="fa fa-sign-in"></i> {{{ trans('global.login-action') }}}
              </a></li>
            @endif
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
                language: <i class="flag-icon flag-icon-{{{ LaravelLocalization::getCurrentLocale() }}}" title="choose language"></i>
                <i class="fa fa-caret-down"></i>
              </a>
              <ul class="dropdown-menu locale-dropdown" role="menu">
                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                  <li>
                    <a rel="alternate" hreflang="{{$localeCode}}" href="{{LaravelLocalization::getLocalizedURL($localeCode) }}">
                      <i class="flag-icon flag-icon-{{{ $localeCode }}}" title="{{{ $properties['native'] }}}"> </i>
                    </a>
                  </li>
                @endforeach
              </ul>
            </li>
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

    {{-- mobile search navbar script --}}
    <script>
    $(function() {
      $('.mobile-search').each(function() {
        var $el = $(this);
        var initial_width = $el.css('width');
        var padding = $el.css('padding-left');

        $el.find('input').focusin(function() {
          $el.css({'opacity': '1', 'width': '100%'});
        }).focusout(function() {
          $el.css({'opacity': '0', 'width': initial_width});
        });
      });
    });
    </script>

    @yield('scripts')

  </body>
</html>
