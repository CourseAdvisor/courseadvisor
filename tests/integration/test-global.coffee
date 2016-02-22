###
  test-global.coffee

  Tests global stuff like homepage, header, footer...
###

{url, screenshot, doXHR, runWithSoftMode} = require './utils.coffee'

# Tests that the homepage loads
casper.test.begin "Loads homepage", 1, (test) ->
  casper.start url("/"), ->
    test.assertHttpStatus(200)
    screenshot("homepage")
  .run ->
    test.done()

# Tests navbar search (submit with button)
casper.test.begin "Navbar search", 1, (test) ->
  casper.start url("/courses")
  casper.waitForSelector "#navbar-search", ->
    casper.fill("#navbar-search", q: "test", false)
    casper.click("#navbar-search .btn")
  casper.waitForSelector ".page", ->
    test.assertUrlMatch(/search\?q=test$/, "Arrived on search page")
  casper.run ->
    test.done()

casper.test.begin "CSRF token access point is secured", 2, (test) ->
  casper.start url('/')
  casper.then ->
    doXHR url('/api/csrf_token')
  casper.waitFor -> @evaluate -> window.TEST_XHR_DONE == true
  casper.then ->
    test.assertTrue((@evaluate -> window.TEST_XHR_RESULT == 'success'), "Can load csrf token from same origin")
  casper.thenOpen 'http://www.google.ch'
  casper.then ->
    doXHR url('/api/csrf_token')
  casper.waitFor -> @evaluate -> window.TEST_XHR_DONE == true
  casper.then ->
    test.assertTrue((@evaluate -> window.TEST_XHR_RESULT == 'failure'), "Cannot load csrf token from foreign origin")
  casper.run -> test.done()

# Tests 404 error page
casper.test.begin "404 error page is shown correctly", 2, (test) ->
  casper.start url('/does_not_exist'), ->
    # test.assertHttpStatus(404) # cannot do this due to casper/phantom limitations
    test.assertTextExists "not found", "Error page describes the error"
    test.assertTextExists "404", "Error code is shown on page"
  runWithSoftMode(test)
