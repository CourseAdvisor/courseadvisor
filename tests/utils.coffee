###

  utils.coffee

  Contains utilities for both casper and frisby tests. All utilities are not designed
  for both environments though.

###

{port} = require "../tmp/tests-config.coffee"

{url} = config = module.exports =

# === shared utilities ===

# website root
  BASE_URL: 'http://local.courseadvisor.ch' +
    if (port && port != 80)
      ':'+port
    else
      ''

# returns a url relative to the website root. ex: url('/dashboard') -> http://.../dashboard
# {path} must start with a '/'
  url: (path) -> config.BASE_URL + path


# === casper-only utilities ===

# saves a screenshot of the current page to tests/screenshots/{name}
  screenshot: (name) -> casper.capture("../screenshots/#{name}")

# logs-in with margarita as {profile}. When {direct} is set to false (default),
# the session first navigates to /login (with the optional {next} parameter).
# When {direct} is set to true, it is assumed that casper is already on the login page.
  login: ({profile, next, direct}) ->
    if !direct
      target = if next then '/login?next='+next else '/login'
      casper.thenOpen url(target)
    casper.then ->
      @fill('form#loginform',
          'username': profile
          'password': profile
        , true) # submit
