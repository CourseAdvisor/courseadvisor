shared = require '../utils-shared.coffee'

# === casper-only utilities ===
utils = module.exports =
  # saves a screenshot of the current page to tests/screenshots/{name}
  screenshot: (name) -> casper.capture("../screenshots/#{name}.png")

  # logs-in with margarita as {profile}. When {direct} is set to false (default),
  # the session first navigates to /login (with the optional {next} parameter).
  # When {direct} is set to true, it is assumed that casper is already on the login page.
  login: ({profile, next, direct}) ->
    if !direct
      target = if next then '/login?next='+next else '/login'
      casper.thenOpen utils.url(target)
    casper.then ->
      @fill('form#loginform',
          'username': profile
          'password': profile
        , true) # submit

  waitForPage: (cb) -> casper.waitFor( ( -> @evaluate -> @_loaded ), cb)

# extend shared utilities
for i in Object.keys(shared)
  utils[i] = shared[i]
