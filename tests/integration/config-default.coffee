casper.options.viewportSize =
  width:  1280,
  height: 1024

casper.on "resource.received", (request) ->
  casper.test.fail "Resource Not Found: #{request.url}" if request.status == 404

casper.test.done()
