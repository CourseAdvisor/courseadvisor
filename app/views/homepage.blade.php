<!DOCTYPE html>
<html lang="en">
  <head>
    @include('header', ['title' => trans('homepage.title')])
  </head>
  <body>
    <div class="container">
      <a href="/" class="navbar-brand pull-left"><img src="//www.epfl.ch/img/epfl_small.png" class="epfl-logo" alt=""> <span class="logo-course">Course</span>Advisor</a>

      <ul class="homepage-header pull-right">
        <li><a href="{{{ action('AuthController@login', ['next' => Request::url()]) }}}">{{{ trans('global.login-action') }}}</a></li>
      </ul>
      <div class="dropdown homepage-header pull-right">
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
      </div>
    </div>
    <section id="splash">
      <div class="container">
        <div class="page">
          <h1>{{{ trans('homepage.heading') }}}</h1>
          <p>
            {{{ trans('homepage.main-text') }}}
            <br>
            <br>
            {{{ trans('homepage.search-invitation') }}}
          </p>
          <div class="hero-search">
            <form action="{{{ action('SearchController@search') }}}" method="GET">
              <div class="input-group input-group-lg hidden-xs">
                <input type="text" class="form-control hero-search-input-lg" name="q" placeholder="{{{ trans('homepage.search-placeholder') }}}">
                <span class="input-group-btn">
                  <button class="btn btn-primary" type="submit">{{{ trans('homepage.search-action') }}}</button>
                </span>
              </div>
            </form>
            <form action="{{{ action('SearchController@search') }}}" method="GET">
              <div class="form-group form-group-lg visible-xs">
                <input type="text" class="form-control hero-search-input" name="q" placeholder="{{{ trans('homepage.search-placeholder') }}}">
              </div>
              <button class="btn btn-primary visible-xs hero-search-button" type="submit">{{{ trans('homepage.search-action') }}}</button>
            </form>
          </div>
          <p>
            {{{ trans('homepage.login-invitation') }}}
          </p>
          <div class="hero-browse">
            <a id="header-login" href="{{{ action('AuthController@login') }}}" class="btn btn-lg btn-primary">
              <i class="fa fa-eye"></i> {{{ trans("global.login-action") }}}
            </a>
          </div>
        </div>
      </div>
    </section>

    @include('footer')

    {{ HTML::script("https://code.jquery.com/jquery-1.10.2.min.js") }}
    {{ HTML::script("js/vendor/bootstrap.min.js") }}

  </body>

</html>
