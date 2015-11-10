###
  votes_spec.coffee

  This spec defines votes api behaviour.
###

{test, logged_in_as, withCSRF} = require './utils'

###
test "Valid vote on review"
  .withUser 'snow'
  .withCSRF
  .is
###
