{BASE_URL} = require './utils.coffee'

casper.options.viewportSize =
  width:  1280
  height: 1024

# For debugging:
casper.on 'remote.message', (message) ->
    @echo "client LOG: #{message}"
casper.on 'remote.error', (message) ->
    @echo "client ERR: #{message}"
#

casper.on "resource.received", (request) ->
  casper.test.fail "Resource Not Found: #{request.url}" if (request.status == 404)

# Cookies are cleared between each run
casper.on "run.complete", -> phantom.clearCookies()

# Casper will confirm all confirm boxes by default
casper.setFilter "page.confirm", -> true

casper.on "url.changed", (url) ->
  if (url.indexOf(BASE_URL) == 0)
    casper.evaluate ->
      window.DEBUG = true

      disableAnimations = ->
        jQuery = window.jQuery
        if ( jQuery )
          jQuery.fx.off = true

        css = document.createElement( "style" )
        css.type = "text/css"
        css.innerHTML = "* { -webkit-transition: none !important; transition: none !important; -webkit-animation: none !important; animation: none !important; }"
        document.body.appendChild( css )

      if ( document.readyState != "loading" )
        disableAnimations()
      else
        window.addEventListener( 'load', disableAnimations, false )

casper.test.done()
