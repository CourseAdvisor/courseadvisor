###
  test-comments.coffee

  Tests admin page
###

{url, screenshot, login, randomStr, waitForPage} = require './utils.coffee'

casper.test.begin "Unauthenticated user cannot access admin page", 2, (test) ->

  casper.start url("/")
  login profile: "cersei", next: "/admin"
  # Creates a review in order to comment
  casper.then ->
    test.assertHttpStatus 200
    test.assertTitleMatches(/Rick Astley/i, "User got rickrolled");
  casper.run ->
    test.done()

casper.test.begin "Authenticated user can access admin page", 3, (test) ->
  casper.start url("/")
  login profile: "snow", next: "/admin"
  # Creates a review in order to comment
  casper.then ->
    test.assertHttpStatus 200
    test.assertTextExists("Administration", "Page contains 'administration' keyword")
    test.assertTitleMatches(/(Admin.*CourseAdvisor|CourseAdvisor.*Admin)/i, "Page title contains Admin and CourseAdvisor")
  casper.run ->
    test.done()
