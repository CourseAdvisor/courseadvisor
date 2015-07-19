port = casper.options.port || 80

casper.on "resource.received", (request) ->
  casper.test.fail "Resource Not Found: #{request.url}" if request.status == 404

config = module.exports =
  BASE_URL: 'http://local.courseadvisor.ch:'+(port || 80),
  url: (path) -> config.BASE_URL + path;