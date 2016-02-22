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

  # Performs an XMLHttpRequest in the context of the page.
  # sets global flags on the window object `window.TEST_XHR_DONE` and `window.TEST_XHR_RESULT`
  # enableing the user to poll for XHR status using casper.evaluate.
  doXHR: (url) ->
    casper.evaluate ((url) ->
      window.TEST_XHR_DONE = false
      window.TEST_XHR_RESULT = null
      xhr = new XMLHttpRequest();
      xhr.open('GET', url)
      xhr.onerror = -> window.TEST_XHR_RESULT = 'failure'
      xhr.onload = -> window.TEST_XHR_RESULT = 'success'
      xhr.onreadystatechange = -> window.TEST_XHR_DONE = (xhr.readyState == 4)
      xhr.send()
    ), url

  # Runs the current test without checking for 404
  runWithSoftMode: (test) ->
    casper.softMode = true
    casper.run ->
      casper.softMode = false
      test.done()

# extend shared utilities
shared.extend(utils, shared)
