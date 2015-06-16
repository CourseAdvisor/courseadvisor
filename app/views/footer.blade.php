{{-- facebook stuff --}}
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/{{{ trans('locale_code') }}}/sdk.js#xfbml=1&version=v2.3";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>

<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-6">
        <ul>
          @if(!Tequila::isLoggedIn())
            <li><a href="{{{ action('CourseController@studyCycles') }}}">{{{ trans('global.browse-courses-action') }}}</a></li>
            <li><a href="{{{ action('AuthController@login') }}}">{{{ trans('global.login-action') }}}</a></li>
          @else
            <li><a href="{{{ action('StudentController@dashboard') }}}">{{{ trans('global.dashboard-action') }}}</a></li>
            <li><a href="{{{ action('AuthController@logout', ['next' => Request::root()]) }}}">{{{ trans('global.logout-action') }}}</a></li>
          @endif
          <li><a href="{{ action('StaticController@about') }}">{{{ trans('global.about-action') }}}</a></li>
        </ul>
      </div>
      <div class="col-xs-12 col-sm-6">
        <div class="social-stuff">
          <h4>{{{ trans('global.social-invite') }}}</h4>
          <ul>
            <li><iframe src="https://ghbtns.com/github-btn.html?user=courseadvisor&repo=courseadvisor&type=star&count=true" frameborder="0" scrolling="0" width="90px" height="20px"></iframe></li>
            <li>
              <div class="fb-like" data-href="https://www.facebook.com/courseadvisor.epfl" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</footer>
