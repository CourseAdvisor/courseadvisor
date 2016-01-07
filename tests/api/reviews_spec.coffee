###
  reviews_spec.coffee

  Tests reviews api special cases. Normal usage is extensively covered in integration tests.
###

{test, url} = require "./utils"


test "Cannot post without a mark"
.on post: "/api/review", followAllRedirects: true, body: { course_instance_id: 737, title: "Lorem ipsum", comment: "dolor sit" }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", "/en/course/neutronics-737"
    .expectBodyContains "Please grade at least"

test "Cannot post without course_instance_id"
.on post: "/api/review", followAllRedirects: true, body: { title: "Lorem ipsum", comment: "dolor sit" }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", "/api/errors"
    .expectBodyContains "course instance id field is required"

test "Cannot post with a comment but without a title"
.on post: "/api/review", followAllRedirects: true, body: { course_instance_id: 737, comment: "dolor sit" }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", "/api/errors"
    .expectBodyContains "title field is required"

test "Cannot post two reviews on same course"
.on post: "/api/review", followAllRedirects: true, body: { course_instance_id: 921, lectures_grade: 3 }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.expectBodyContains "already reviewed this course."

test "Cannot give out of bounds grades"
.on post: "/api/review", followAllRedirects: true, body: { course_instance_id: 737, lectures_grade: -1, exercises_grade: 6 }
.withCSRF().withUser "snow"
.is (rq) ->
  rq.addHeader "referer", "/api/errors"
    .expectBodyContains "The lectures grade must be between 0 and 5"
    .expectBodyContains "The exercises grade must be between 0 and 5"

test "Cannot post review without CSRF"
.on post: "/api/review", followAllRedirects: true, body: { course_instance_id: 737, lectures_grade: 2 }
.withUser "snow"
.is (rq) -> rq.expectStatus 500
