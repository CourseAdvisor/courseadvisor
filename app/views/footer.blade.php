<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-lg-7 col-sm-4 col-xs-12">
        <ul>
          @if(!Tequila::isLoggedIn())
            <li><a href="{{{ action('CourseController@studyCycles') }}}">{{{ trans('global.browse-courses-action') }}}</a></li>
            <li><a href="{{{ action('AuthController@login') }}}">{{{ trans('global.login-action') }}}</a></li>
          @else
            <li><a href="{{{ action('StudentController@dashboard') }}}">{{{ trans('global.dashboard-action') }}}</a></li>
            <li><a href="{{{ action('AuthController@logout', ['next' => Request::url()]) }}}">{{{ trans('global.logout-action') }}}</a></li>
          @endif
          <li><a href="#">{{{ trans('global.about-action') }}}</a></li>
        </ul>
      </div>
      <div class="col-lg-5 col-md-5 col-sm-8 col-xs-10 copyright">
        Ⓢ Satelliteware 2015, <a href="http://christophetd.fr">christophetd</a>, <a href="http://hmil.fr">hmil</a> and <a href="http://rickrolled.fr">contributors</a>.
      </div>
    </div>
  </div>
</footer>
