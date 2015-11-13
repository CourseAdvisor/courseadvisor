log = require('./log')

module.exports = events = window.events =
  trigger: (evt) ->
    log.v("triggered: #{evt}")
    window.triggered[evt] = true
    if listeners[evt]?
      for i in listeners[evt]
        i()

  clear: (evt) ->
    delete window.triggered[evt]
  poll: (evt) ->
    window.triggered[evt]

  on: (evt, cb) ->
    listeners[evt] = [] if !listeners[evt]?
    listeners[evt].push(cb)

listeners = {}
window.triggered = {}
