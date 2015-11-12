###
  votes_spec.coffee

  This spec defines votes api behaviour.
###

{test, logged_in_as, withCSRF} = require './utils'


# Normal usage

test "Authorized vote on own review"
.on "/api/vote", method: "post", data: { type: "up", review: 485 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 200

test "Vote up on own review"
.on "/api/vote", method: "post", data: {type: "up", review: 485 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 200

test "Vote down on own review"
.on "/api/vote", method: "post", data: {type: "down", review: 485 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 200

test "Vote on own comment"
.on "/api/vote", method: "post", data: { type: "up", comment: 5 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 200

test "Vote on foreign comment"
.on "/api/vote", method: "post", data: { type: "up", comment: 5 }
.withCSRF().withAJAX().withUser 'cersei'
.is (rq) -> rq.expectStatus 200

test "Vote up on foreign review"
.on "/api/vote", method: "post", data: { type: "up", review: 485 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 200


# Bad usage

test "Unauthenticated vote"
.on "/api/vote", method: "post", data: { type: "up", review: 485 }
.withCSRF().withAJAX()
.is (rq) -> rq.expectStatus 401

test "Vote on inexistant review"
.on "/api/vote", method: "post", data: { type: "up", review: 1000 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote on review with wrong direction"
.on "/api/vote", method: "post", data: { type: "foo", review: 1000 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote on inexistant comment"
.on "/api/vote", method: "post", data: { type: "up", comment: 1000 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote on comment with wrong direction"
.on "/api/vote", method: "post", data: { type: "bar", comment: 5 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote without type"
.on "/api/vote", method: "post", data: { comment: 5 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote without comment"
.on "/api/vote", method: "post", data: { type: "up" }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400
