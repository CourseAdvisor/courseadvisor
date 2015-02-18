<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>
    @section('page_title')
    Course Advisor
    @show
    </title>

    {{ HTML::style('css/bootstrap.css') }}

    <link href="/css/variables.less" rel="stylesheet/less">
    <link href="/css/bootswatch.less" rel="stylesheet/less">
  </head>
  <body>

  <div class="navbar navbar-default navbar-static-top">
    <div class="container">
      <div class="navbar-header">
        <a href="../" class="navbar-brand">Course advisor</a>
      </div>
    <div class="navbar-collapse collapse" id="navbar-main">
      <ul class="nav navbar-nav">
      <li>
        <a href="">Home</a>
      </li>
      <li>
        <a href="{{{ action('CourseController@index') }}}">
          Courses
        </a>
      </li>
      <li>
        <a href="{{{ action('StudentController@index') }}}">
          Students
        </a>
      </li>
      </ul>

      <ul class="nav navbar-nav navbar-right">
        @if(Tequila::isLoggedIn())
          <li><a href="#">{{{ Tequila::get('firstname') }}}</a></li>
          <li><a href="{{{ action('AuthController@logout', ['next' => Request::url()]) }}}">logout</a></li>
        @else
          <li><a href="{{{ action('AuthController@login', ['next' => Request::url()]) }}}">login</a></li>
        @endif
      </ul>

    </div>
    </div>
  </div>

  <div class="container">
    @if(Session::has('message'))
      <div class="alert alert-{{{ Session::get('message.type', 'info') }}}" role="alert">
        {{{ Session::get('message.message', Session::get('message')) }}}
      </div>
    @endif
    
    @yield('content')
  </div>

    {{ HTML::script('js/less-1.7.0.js') }}
    {{ HTML::script('js/dropdown.js') }}
  </body>
</html>
