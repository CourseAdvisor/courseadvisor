###
  comments_spec.coffee

  Defines comments API behaviour
###

{test, url} = require "./utils"

# Normal usage

test "Post a new comment"
.on post: "/api/comment", followAllRedirects: true, data: { review_id: 2, body: "Valar morghulis" }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", url("/en/course/chemistry-of-food-processes-899")
    .expectStatus 200
    .expectBodyContains "Valar morghulis"
    .expectBodyContains "comment has been posted"

test "Edit a comment"
.on post: "/api/comment/edit", followAllRedirects: true, data: { comment_id: 6, body: "Valar dohaeris" }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", url("/en/course/chemistry-of-food-processes-899")
    .expectStatus 200
    .expectBodyContains "Valar dohaeris"
    .expectBodyContains "successfuly updated"

test "Reply to a comment"
.on post: "/api/comment", followAllRedirects: true, data: { parent_id: 6, review_id: 2, body: "You know nothing" }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", url("/en/course/chemistry-of-food-processes-899")
    .expectStatus 200
    .expectBodyContains "You know nothing"
    .expectBodyContains "comment has been posted"


# Bad usage

test "Comment without CSRF"
.on post: "/api/comment", followAllRedirects: true, data: { review_id: 2, body: "Valar morghulis" }
.withUser "snow"
.is (rq) -> rq.expectStatus 500

test "Edit comment without CSRF"
.on post: "/api/comment/edit", followAllRedirects: true, data: { comment_id: 6, body: "Valar dohaeris" }
.withUser "snow"
.is (rq) -> rq.expectStatus 500

test "Comment body is required"
.on post: "/api/comment", followAllRedirects: true, data: { review_id: 2 }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", url("/api/errors")
    .expectBodyContains "body field is required"

test "Cannot post orphean comment"
.on post: "/api/comment", followAllRedirects: true, data: { body: "Valar dohaeris" }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", url("/api/errors")
    .expectBodyContains "review id field is required"
