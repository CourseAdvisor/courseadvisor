###
  test-login.coffee

  Tests basic login features
###

{url, screenshot, login} = require("../utils.coffee")

# Tests that a login without parameters directs to the homepage
casper.test.begin "Login lands on dashboard", 1, (test) ->
  casper.start url("/")
  login profile: "snow"
  casper.then ->
    test.assertMatch(@getCurrentUrl(), /\/[a-z]{2}\/dashboard$/, "landed on dashboard")
  .run ->
    test.done()

# Tests login and then logout
casper.test.begin "Login with next parameter and then logout", 3, (test) ->
  casper.start url("/fr/course/psychologie-sociale-a-450"), ->
    @click "#header-login"
  login profile: "snow", direct: true
  casper.then ->
    test.assertEquals(@getCurrentUrl(), url("/fr/course/psychologie-sociale-a-450"), "Stays on the same page")
    test.assertExists("#logged-in-menu", "Is logged in")
    @click 'footer a[href*="logout"]'
  .then ->
    test.assertDoesntExist("#logged-in-menu", "Is logged out")
  .run ->
    test.done()
