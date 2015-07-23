###
  global_api_spec.coffee

  This spec defines global API behaviors. Focused specs are available in their
  respective files.
###

# Basic requirements
frisby = require "frisby"
{url} = require("../utils")


###
  The following behaviors derive from the fact that the private API should be
  protected behind the common auth filter. An unauthorized call to any private API
  route should have the following behavior.
###

frisby.create("An ajax call to the auth probe route should result in an unauthorized response")
  .addHeaders "X-Requested-With": "XMLHttpRequest"
  .get( url("/api/is_auth") )
  .expectStatus(401)
.toss();

frisby.create("A call to the auth probe route should result in a redirect to tequila")
  .get( url("/api/is_auth"), followRedirect: false )
  .expectStatus(302)
.toss();
