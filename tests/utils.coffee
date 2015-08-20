{port} = require "../tmp/tests-config.coffee"

config = module.exports =
  BASE_URL: 'http://local.courseadvisor.ch:'+(port || 80),
  url: (path) -> config.BASE_URL + path,
  screenshot: (name) -> casper.capture("../screenshots/#{name}")
