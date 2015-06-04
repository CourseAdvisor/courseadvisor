<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <ul>
          @if(!Tequila::isLoggedIn())
            <li><a href="{{{ action('CourseController@studyCycles') }}}">{{{ trans('global.browse-courses-action') }}}</a></li>
            <li><a href="{{{ action('AuthController@login') }}}">{{{ trans('global.login-action') }}}</a></li>
          @else
            <li><a href="{{{ action('StudentController@dashboard') }}}">{{{ trans('global.dashboard-action') }}}</a></li>
            <li><a href="{{{ action('AuthController@logout', ['next' => Request::root()]) }}}">{{{ trans('global.logout-action') }}}</a></li>
          @endif
          <li><a href="{{ action('StaticController@about') }}">{{{ trans('global.about-action') }}}</a></li>
          <li class="pull-right"><iframe src="https://ghbtns.com/github-btn.html?user=courseadvisor&repo=courseadvisor&type=star&count=true" frameborder="0" scrolling="0" width="170px" height="20px"></iframe></li>
        </ul>
      </div>
    </div>
  </div>
</footer>
