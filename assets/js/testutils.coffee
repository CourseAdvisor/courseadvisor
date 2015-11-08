# Signals page fully loaded after all ready callbacks have been executed
$ -> setTimeout( (-> window._loaded = true), 1)
