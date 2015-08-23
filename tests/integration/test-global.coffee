###
  test-global.coffee

  Tests global stuff like homepage, header, footer...
###

{url, screenshot} = require("../utils.coffee")

# Tests that the homepage loads
casper.test.begin "Loads homepage", 1, (test) ->
  casper.start url("/"), ->
    test.assertHttpStatus(200)
    screenshot("homepage")
  .run ->
    test.done()
