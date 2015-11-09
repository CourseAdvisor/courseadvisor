###
  global_api_spec.coffee

  This spec defines global API behaviors. Detailed specs are available in their
  respective spec files.
###

# Basic requirements
{test, logged_in_as} = require("./utils")

# An ajax call to the auth probe route should result in an unauthorized response
test "Auth probe unauthorized AJAX"
.on "/api/is_auth"
.is (rq) ->
  rq.addHeaders "X-Requested-With": "XMLHttpRequest"
    .expectStatus 401
.toss()

# A call to the auth probe route should result in a redirect to tequila
test "Auth probe unauthorized"
.on "/api/is_auth", followRedirect: false
.is (rq) ->
  rq.expectStatus 302
.toss()


logged_in_as 'snow', (test) ->

  # A call to the auth probe route when authorized shoud return 200
  test "Auth probe authorized"
  .on "/api/is_auth", followRedirect: false
  .is (rq) ->
    rq.expectStatus 200
  .toss()
