###
  global_api_spec.coffee

  This spec defines global API behaviors. Detailed specs are available in their
  respective spec files.
###

{test} = require './utils'

# Call to wrong routes result in 404 error code
test "Call to an incorrect route yields a 404"
.on "/api/does_not_exist"
.is (rq) -> rq.expectStatus 404

# An ajax call to the auth probe route should result in an unauthorized response
test "Auth probe unauthorized AJAX"
.on "/api/is_auth"
.withAJAX()
.is (rq) -> rq.expectStatus 401

# A call to the auth probe route should result in a redirect to tequila
test "Auth probe unauthorized"
.on "/api/is_auth", followRedirect: false
.is (rq) -> rq.expectStatus 302

# A call to the auth probe route when authorized shoud return 200
test "Auth probe authorized"
.withUser "snow"
.on "/api/is_auth", followRedirect: false
.is (rq) -> rq.expectStatus 200
