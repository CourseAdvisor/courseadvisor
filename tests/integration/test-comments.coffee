###
  test-comments.coffee

  Tests comment features
###

{url, screenshot, login, randomStr, waitForPage} = require("../utils.coffee")

# Tests basic comment workflow:
# - post a comment
#   - mandatory grade
#   - mandatory title if content set
# - edit a review (with error on title)
# - delete a Review
casper.test.begin "Full comment workflow", 6, (test) ->
  content = randomStr(128)
  title = randomStr(32)
  comment = randomStr(128)

  getFirstReviewVotes = -> parseInt($('.review:first-child .comment:first-child [data-vote-score]').first().text())
  votes = 0
  lastTime = 0

  casper.start url("/")
  login profile: "snow", next: "/fr/course/psychologie-sociale-d-524"
  # Creates a review in order to comment
  waitForPage ->
    @click("[data-starbar*=lectures_grade]>.fa-stack:nth-child(3)")
  casper.then ->
    @fill("form#create-review-form",
      comment: content
      title: title
      difficulty: "2"
    , true) # submit form
  waitForPage ->
    @click "[data-comment-action^=reply]"
  casper.then ->
    @fill("form[action$=comment]",
      body: comment
    , true )
  waitForPage ->
    test.assertTextExists(comment, "Comment is shown")
    @click "[data-comment-action^=edit]"
  casper.then ->
    @fill('form[action$="comment/edit"]',
      body: "#{comment}_edited"
    , true )
  waitForPage ->
      test.assertTextExists("#{comment}_edited", "Modified comment is shown")
      # test votes
      votes = @evaluate getFirstReviewVotes
      @evaluate -> @events.clear('vote.completed')
      @click('.review:first-child .comment:first-child [data-vote-btn^="up"]')
  casper.waitFor( ( -> @evaluate -> @events.poll('vote.completed') ), ->
    test.assertEvalEquals(getFirstReviewVotes, votes + 1, "Vote up increases the comment mark")
    @evaluate -> @events.clear('vote.completed')
    @click('.review:first-child .comment:first-child [data-vote-btn^="down"]')
  )
  casper.waitFor( ( -> @evaluate -> @events.poll('vote.completed') ), ->
    test.assertEvalEquals(getFirstReviewVotes, votes - 1, "Vote down decreases the comment mark")
    @evaluate -> @events.clear('vote.completed')
    @click('.review:first-child .comment:first-child [data-vote-btn^="down"]')
  )
  casper.waitFor( ( -> @evaluate -> @events.poll('vote.completed') ), ->
    test.assertEvalEquals(getFirstReviewVotes, votes, "Re-clicking the same vote button discards the vote")
    @click '[data-comment-action^="edit"]'
  )
  casper.then ->
    @click '[formaction$="comment/delete"]'
  waitForPage ->
    test.assertTextDoesntExist(comment, "Comment is gone")
    @click("a.edit-review")
  casper.waitForSelector ".modal-open", ->
    # Edit review modal is open
    @click('[data-action="delete-review"]')
  casper.run ->
    test.done()
