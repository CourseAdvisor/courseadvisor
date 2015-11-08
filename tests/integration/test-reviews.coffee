###
  test-reviews.coffee

  Tests review features
###

{url, screenshot, login, randomStr} = require("../utils.coffee")

# Tests basic review workflow:
# - post a Review
#   - mandatory grade
#   - mandatory title if content set
# - edit a review (with error on title)
# - delete a Review
casper.test.begin "Full review workflow", 19, (test) ->
  content = randomStr(128)
  title = randomStr(32)

  getFirstReviewVotes = -> parseInt($('.review:first-child [data-vote-score]').first().text())
  votes = 0
  lastTime = 0

  casper.start url("/")
  login profile: "snow", next: "/fr/course/psychologie-sociale-d-524"
  casper.waitForSelector "#reviews", ->
    @fill("form#create-review-form",
      comment: content
      difficulty: "2"
    , true) # submit form
  casper.waitForSelector "[data-starbar*=content_grade]>.fa-stack:nth-child(5)", ->
    # Title is mandatory if content is set
    test.assertExists(".form-group.has-error>input[name=title]+.help-block", "Title field has error")
    # Grade at least one criteria
    test.assertExists("[name=review-grades]>.error", "Grades have error")
    # Previous values are retained
    test.assertFieldCSS("form#create-review-form [name=comment]", content, "Comment value is retained on submit error")
    test.assertFieldCSS("form#create-review-form [name=difficulty]", "2", "Difficulty value is retained on submit error")

    @click("[data-starbar*=lectures_grade]>.fa-stack:nth-child(3)")
    @click("[data-starbar*=exercises_grade]>.fa-stack:nth-child(4)")
    @click("[data-starbar*=content_grade]>.fa-stack:nth-child(5)")

    @fill("form#create-review-form",
      title: title
    , true) # submit form
  casper.waitForSelector ".review", ->
    # Review is now posted
    test.assertTextExists(title, "Title is shown on course page")
    test.assertTextExists(content, "Content is shown on course page")
    test.assertExists('.review-author>a[href="http://people.epfl.ch/115687"]', "Author is shown on page")
    @click("a.edit-review")
  casper.waitForSelector ".modal-open", ->
    # Edit dialog is opened, we check that the grades were correctly assigned
    test.assertFieldCSS("#edit-review-form [name=lectures_grade]", "2", "Lectures grade is set correctly")
    test.assertFieldCSS("#edit-review-form [name=exercises_grade]", "3", "Exercises grade is set correctly")
    test.assertFieldCSS("#edit-review-form [name=content_grade]", "4", "Content grade is set correctly")
    # create an edit error
    @fill("form#edit-review-form",
      title: ''
      comment: "#{content}_edited"
    , true ) # submit form
  # Submitted an erroneous update, should open dialog and show error
  casper.waitForSelector ".modal-open", ->
    # Edit review modal is open
    screenshot("edit-review")
    test.assertExists(".form-group.has-error>input[name=title]+.help-block", "Title field has error")
    @fill("form#edit-review-form",
      title: "#{title}_edited"
    , true ) # submit form
  casper.waitForSelector "[data-starbar*=content_grade]>.fa-stack:nth-child(5)", ->
    # Review is now edited
    test.assertTextExists("#{title}_edited", "Edited title is shown on course page")
    test.assertTextExists("#{content}_edited", "Edited content is shown on course page")

    # test votes
    votes = @evaluate getFirstReviewVotes
    @click('.review:first-child [data-vote-btn^="up"]')
    lastTime = new Date()
  casper.waitForResource (res) ->
    if (/vote$/.test(res.url))
      console.log("my "+res.time.getTime()+" yours "+lastTime.getTime())
      res.time > lastTime
    else false
  casper.wait 500, ->
    test.assertEvalEquals(getFirstReviewVotes, votes + 1, "Vote up increases the review mark")
    @click('.review:first-child [data-vote-btn^="down"]')
    lastTime = new Date()
  casper.waitForResource (res) ->
    if (/vote$/.test(res.url))
      console.log("my "+res.time.getTime()+" yours "+lastTime.getTime())
      res.time > lastTime
    else false
  casper.wait 500, ->
      test.assertEvalEquals(getFirstReviewVotes, votes - 1, "Vote down decreases the review mark")
      @click('.review:first-child [data-vote-btn^="down"]')
      lastTime = new Date()
  casper.waitForResource (res) ->
    if (/vote$/.test(res.url))
      console.log("my "+res.time.getTime()+" yours "+lastTime.getTime())
      res.time > lastTime
    else false
  casper.wait 500, ->
      test.assertEvalEquals(getFirstReviewVotes, votes, "Re-clicking the same vote button discards the vote")

      # remove the review
      @click("a.edit-review")
  casper.waitForSelector ".modal-open", ->
    # Edit review modal is open
    @click('[data-action="delete-review"]')
  casper.waitForSelector "#reviews", ->
    # Review has been deleted
    test.assertTextDoesntExist(title, "Title is gone")
    test.assertTextDoesntExist(content, "Content is gone")
    test.assertDoesntExist("a.edit-review", "Edit review action is not shown")
  casper.run ->
    test.done()
