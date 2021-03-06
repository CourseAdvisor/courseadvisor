###
  test-reviews.coffee

  Tests review features
###

{url, screenshot, login, randomStr, waitForPage} = require './utils.coffee'

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

  casper.start url("/")
  login profile: "snow", next: "/fr/course/psychologie-sociale-d-524"
  waitForPage ->
    @fill("form#create-review-form",
      comment: content
      difficulty: "2"
    , true) # submit form
  waitForPage ->
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
  waitForPage ->
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
  waitForPage ->
    # Review is now edited
    test.assertTextExists("#{title}_edited", "Edited title is shown on course page")
    test.assertTextExists("#{content}_edited", "Edited content is shown on course page")

    # test votes
    votes = @evaluate getFirstReviewVotes
    @evaluate -> @events.clear('vote.completed')
    @click('.review:first-child [data-vote-btn^="up"]')
    lastTime = new Date()
  casper.waitFor( ( -> @evaluate -> @events.poll('vote.completed') ), ->
      test.assertEvalEquals(getFirstReviewVotes, votes + 1, "Vote up increases the review mark")
      @evaluate -> @events.clear('vote.completed')
      @click('.review:first-child [data-vote-btn^="down"]')
  )
  casper.waitFor( ( -> @evaluate -> @events.poll('vote.completed') ), ->
      test.assertEvalEquals(getFirstReviewVotes, votes - 1, "Vote down decreases the review mark")
      @evaluate -> @events.clear('vote.completed')
      @click('.review:first-child [data-vote-btn^="down"]')
  )
  casper.waitFor( ( -> @evaluate -> @events.poll('vote.completed') ), ->
      test.assertEvalEquals(getFirstReviewVotes, votes, "Re-clicking the same vote button discards the vote")
      # remove the review
      @click("a.edit-review")
  )
  casper.waitForSelector ".modal-open", ->
    # Edit review modal is open
    @click('[data-action="delete-review"]')
  waitForPage ->
    # Review has been deleted
    test.assertTextDoesntExist(title, "Title is gone")
    test.assertTextDoesntExist(content, "Content is gone")
    test.assertDoesntExist("a.edit-review", "Edit review action is not shown")
  casper.run ->
    test.done()

casper.test.begin "Test my review edit link", 4, (test) ->
  casper.start url("/")
  login profile: "snow", next: "/en/course/concrete-bridges-921"
  waitForPage ->
    test.assertExists('[data-review-id="1"]', "'My review' edit link exists")
    @click('[data-review-id="1"]')
  casper.waitForSelector ".modal-open", ->
    test.assertField("title", "Still more interesting than a game of bridge")
    test.assertField("comment", "This course bridges the gap between plans and their concrete implementation.")
    @fill("form#edit-review-form",
      title: "Still more interesting than two games of bridge"
    , true ) # submit form
  waitForPage ->
    test.assertTextExist("Still more interesting than two games of bridge", "Title has been updated")
    @click('[data-review-id="1"]')
  casper.waitForSelector ".modal-open", ->
    # Restore state to original to allow reiterable tests
    @fill("form#edit-review-form",
      title: "Still more interesting than a game of bridge"
    , true ) # submit form
  casper.run -> test.done()
