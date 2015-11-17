log = require("./log")

# Signals page fully loaded after all ready callbacks have been executed
$ ->
  log.v("Loaded page: #{document.location.href}")
  setTimeout( (-> window._loaded = true), 1)
