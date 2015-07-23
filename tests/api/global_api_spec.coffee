###
  api-example_spec.coffee

  This file demonstrate how to test the APIs using frisby and jasmine. It should
  be refactored in the near future when we find a proper way to organize tests
  in this folder.
###

# Basic requirements
frisby = require "frisby"
{url} = require("../utils")

# An ajax call to the auth probe route should result in an unauthorized response
frisby.create("Test auth probe unauthorized AJAX")
  .addHeaders "X-Requested-With": "XMLHttpRequest"
  .get( url("/api/is_auth") )
  .expectStatus(401)
.toss();

# A call to the auth probe route should result in a redirect to tequila
frisby.create("Test auth probe unauthorized")
  .get( url("/api/is_auth"), followRedirect: false )
  .expectStatus(301)
.toss();
