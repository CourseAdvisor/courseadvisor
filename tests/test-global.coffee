###
  test-global.coffee

  Tests global stuff like homepage, header, footer...
###

url = require('./config.coffee').url

casper.test.begin 'Loads homepage', 1, (test) ->
  casper.start url('/'), ->
    test.assertHttpStatus(200)
  .run ->
    test.done()
