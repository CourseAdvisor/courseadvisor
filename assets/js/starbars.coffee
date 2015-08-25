###
  starabar.coffee

  Handles data-starbar attributes using the lib starbar.js
###

# Initialize starbars
$ ->
  $('[data-starbar]').each ->
    el = $(@)

    # Parse starbar attributes
    data = el.attr('data-starbar').split(',');
    attrs =
      inputName: data.splice(0, 1)[0]

    for arg in data
      splitted = arg.split('=')
      attrs[splitted[0]] = splitted[1] || true

    starbar = new StarBar(el, attrs)
    initialValue = el.attr('data-value')
    starbar.setValue(initialValue) if initialValue?

    el.data('starbar', starbar)
