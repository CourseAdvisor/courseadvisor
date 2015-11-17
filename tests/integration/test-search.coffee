###
  test-search.coffee

  Tests search features
###

{url, screenshot, login, randomStr} = require './utils.coffee'

casper.test.begin "Search for a teacher", 2, (test) ->
  casper.start url('/search?q=rené+beuchat')
  casper.waitForSelector ".page", ->
    test.assertTextExists("Embedded systems", "Displays taught courses")
    test.assertTextExists("Microelectronics", "Displays taught courses")
  casper.run ->
    test.done()

casper.test.begin "Search for a course", 1, (test) ->
  casper.start url('/search?q=compiler+construction')
  casper.waitForSelector ".page", ->
    test.assertTextExists("Advanced compiler construction", "Displays results")
  casper.run ->
    test.done()
