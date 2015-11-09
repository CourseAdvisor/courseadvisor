###

  utils.coffee

  Contains utilities for both casper and frisby tests. All utilities are not designed
  for both environments though.

###

{port} = require "../tmp/tests-config.coffee"

# Dictionnary for randomStr
DICT = " abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ 0123456789 "

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

  # generates a random (hopefully unique) string of length {len}
  randomStr: (len = 32) ->
    return (DICT.charAt(Math.random()*DICT.length) for i in [0..len]).join('')

  # === casper-only utilities ===

  # saves a screenshot of the current page to tests/screenshots/{name}
  screenshot: (name) -> casper.capture("../screenshots/#{name}.png")

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

  waitForPage: (cb) -> casper.waitFor( ( -> @evaluate -> @_loaded ), cb)
