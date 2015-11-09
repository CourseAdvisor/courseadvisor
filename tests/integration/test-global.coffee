###
  test-global.coffee

  Tests global stuff like homepage, header, footer...
###

{url, screenshot} = require './utils.coffee'

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
