###
  votes_spec.coffee

  This spec defines votes api behaviour.
###

{test, logged_in_as, withCSRF} = require './utils'


test "Unauthenticated vote"
  .on "/api/vote", method: 'post'
  .withCSRF
  .withAJAX
  .is (rq) -> rq.expectStatus 401
