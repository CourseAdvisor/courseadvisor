<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">

    <title>{{ trans('homepage.title') }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    {{ HTML::style("css/courseadvisor.css") }}
    {{ HTML::style("css/font-awesome.min.css") }}
  </head>
  <body>
    <div class="container">
      <a href="/" class="navbar-brand pull-left"><img src="//www.epfl.ch/img/epfl_small.png" class="epfl-logo" alt=""> <span class="logo-course">Course</span>Advisor</a>

      <ul class="homepage-login pull-right">
        <li><a href="{{{ action('AuthController@login', ['next' => Request::url()]) }}}">Log in</a></li>
      </ul>
    </div>
    <section id="splash">
      <div class="container">
        <div class="page">
          <h1>Welcome!</h1>
          <p>
            Courseadvisor is a service dedicated to helping EPFL students chose their courses.<br>
            <br>
            Lookup a course and find out what past students thought about it.
          </p>
          <div class="hero-search">
            <form action="{{{ action('SearchController@search') }}}" method="GET">
              <div class="input-group input-group-lg hidden-xs">
                <input type="text" class="form-control hero-search-input-lg" name="q" placeholder="Search a course by title, field, teacher, ...">
                <span class="input-group-btn">
                  <button class="btn btn-primary" type="submit">Go!</button>
                </span>
              </div>
            </form>
            <form action="{{{ action('SearchController@search') }}}" method="GET">
              <div class="form-group form-group-lg visible-xs">
                <input type="text" class="form-control hero-search-input" name="q" placeholder="Search a course by title, field, teacher, ...">
              </div>
              <button class="btn btn-primary visible-xs hero-search-button" type="submit">Go!</button>
            </form>
          </div>
          <p>
            Or log-in with your gaspar ID to instantly find relevant courses!
          </p>
          <div class="hero-browse">
            <a href="{{{ action('AuthController@login') }}}" class="btn btn-lg btn-primary"><i class="fa fa-eye"></i> Log-in</a>
          </div>
        </div>
      </div>
    </section>

    @include('footer')

  </body>

</html>

