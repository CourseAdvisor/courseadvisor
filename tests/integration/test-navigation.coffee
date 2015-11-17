###
  test-navigation.coffee

  Tests site navigation
###

{url, screenshot, login} = require './utils.coffee'

# Tests basic en course browsing
casper.test.begin "Browse courses (en)", 4, (test) ->
  casper.start url("/en/courses"), ->
    test.assertElementCount("#course_list>a", 4, "Shows cycle list") # propé, bachelor, master, minor
    screenshot("cycle_list_en")
    @click "#course_list>a"
  .then ->
    test.assertTextExists("Propedeutics plans", "Shows propedeutics plans page")
    test.assertElementCount("#course_list>a", 14, "Shows all plans")
    screenshot("prope_plans_en")
    @click "#course_list>a"
  .then ->
    test.assertExists("#course_list>a", "Shows at least one course")
    screenshot("course_list_en")
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
