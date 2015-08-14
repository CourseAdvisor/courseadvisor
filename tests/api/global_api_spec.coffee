###
  global_api_spec.coffee

  This spec defines global API behaviors. Detailed specs are available in their
  respective spec files.
###

# Basic requirements
frisby = require "frisby"
{url} = require("../utils")

# An ajax call to the auth probe route should result in an unauthorized response
frisby.create("Test auth probe unauthorized AJAX")
  .addHeaders "X-Requested-With": "XMLHttpRequest"
  .get( url("/api/is_auth") )
  .expectStatus(401)
  .timeout(30000)
.toss();

# A call to the auth probe route should result in a redirect to tequila
frisby.create("Test auth probe unauthorized")
  .get( url("/api/is_auth"), followRedirect: false )
  .expectStatus(302)
  .timeout(30000)
.toss();
