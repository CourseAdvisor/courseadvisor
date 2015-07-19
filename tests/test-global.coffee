###
  test-global.coffee

  Tests global stuff like homepage, header, footer...
###

url = require("./web.coffee").url

# Tests that the homepage loads
casper.test.begin "Loads homepage", 1, (test) ->
  casper.start url("/"), ->
    test.assertHttpStatus(200)
    this.capture("screenshots/homepage.png")
  .run ->
    test.done()

# Tests basic en course browsing
casper.test.begin "Browse courses (en)", 4, (test) ->
  casper.start url("/en/courses"), ->
    test.assertElementCount("#course_list>a", 4, "Shows cycle list") # propé, bachelor, master, minor
    @click "#course_list>a"
  .then ->
    test.assertTextExists("Propedeutics plans", "Shows propedeutics plans page")
    test.assertElementCount("#course_list>a", 14, "Shows all plans")
    @click "#course_list>a"
  .then ->
    test.assertExists("#course_list>a", "Shows at least one course")
  .run ->
    test.done()

# Tests basic fr course browsing
casper.test.begin "Browse courses (fr)", 4, (test) ->
  casper.start url("/fr/courses"), ->
    test.assertElementCount("#course_list>a", 4, "Shows cycle list") # prope, bachelor, master, minor
    @click "#course_list>a"
  .then ->
    test.assertTextExists("Plans d'étude de Propedeutique", "Shows propedeutics plans page")
    test.assertElementCount("#course_list>a", 14, "Shows all plans")
    @click "#course_list>a"
  .then ->
    test.assertExists("#course_list>a", "Shows at least one course")
  .run ->
    test.done()