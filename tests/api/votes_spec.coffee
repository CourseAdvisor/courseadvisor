###
  votes_spec.coffee

  This spec defines votes api behaviour.
###

{test} = require './utils'


# Normal usage

test "Vote up on own review"
.on post: "/api/vote", data: {type: "up", review: 1 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) ->
  rq.expectStatus 200
    .expectJSON {score: 1, cancelled: false}

test "Change vote up on own review"
.on post: "/api/vote", data: {type: "down", review: 1 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) ->
  rq.expectStatus 200
    .expectJSON {score: -1, cancelled: false}

test "Cancel vote on own review"
.on post: "/api/vote", data: {type: "down", review: 1 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) ->
  rq.expectStatus 200
    .expectJSON {score: 0, cancelled: true}

test "Vote on own comment"
.on post: "/api/vote", data: { type: "up", comment: 5 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) ->
  rq.expectStatus 200
    .expectJSON {score: 1, cancelled: false}

test "Cancel vote on own comment"
.on post: "/api/vote", data: { type: "up", comment: 5 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) ->
  rq.expectStatus 200
    .expectJSON {score: 0, cancelled: true}

test "Vote on foreign comment"
.on post: "/api/vote", data: { type: "down", comment: 5 }
.withCSRF().withAJAX().withUser 'cersei'
.is (rq) ->
  rq.expectStatus 200
    .expectJSON {score: -1, cancelled: false}

test "Cancel vote on foreign comment"
.on post: "/api/vote", data: { type: "down", comment: 5 }
.withCSRF().withAJAX().withUser 'cersei'
.is (rq) ->
  rq.expectStatus 200
    .expectJSON {score: 0, cancelled: true}


# Bad usage

test "Vote without CSRF"
.on post: "/api/vote", data: { type: "up", review: 1 }
.withAJAX().withUser "snow"
.is (rq) -> rq.expectStatus 500

test "Unauthenticated vote"
.on post: "/api/vote", data: { type: "up", review: 1 }
.withCSRF().withAJAX()
.is (rq) -> rq.expectStatus 401

test "Vote on inexistant review"
.on post: "/api/vote", data: { type: "up", review: 1000 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote on review with wrong direction"
.on post: "/api/vote", data: { type: "foo", review: 1000 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote on inexistant comment"
.on post: "/api/vote", data: { type: "up", comment: 1000 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote on comment with wrong direction"
.on post: "/api/vote", data: { type: "bar", comment: 5 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote without type"
.on post: "/api/vote", data: { comment: 5 }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400

test "Vote without comment"
.on post: "/api/vote", data: { type: "up" }
.withCSRF().withAJAX().withUser 'snow'
.is (rq) -> rq.expectStatus 400
