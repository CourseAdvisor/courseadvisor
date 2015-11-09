###

  utils.coffee

  Contains utilities for both casper and frisby tests. All utilities are not designed
  for both environments though.

###

{port} = require "../tmp/tests-config.coffee"

# Dictionnary for randomStr
DICT = " abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ 0123456789 "

utils = module.exports =

  # === shared utilities ===

  # website root
  BASE_URL: 'http://local.courseadvisor.ch' +
    if (port && port != 80)
      ':'+port
    else
      ''

  # returns a url relative to the website root. ex: url('/dashboard') -> http://.../dashboard
  # {path} must start with a '/'
  url: (path) -> utils.BASE_URL + path

  # generates a random (hopefully unique) string of length {len}
  randomStr: (len = 32) ->
    return (DICT.charAt(Math.random()*DICT.length) for i in [0..len]).join('')
