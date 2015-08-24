# Initialize popovers
$ ->
  popovers = $("[data-toggle=popover]")
  popovers.popover()

  $('#search-icon').click ->
    $(@).fadeOut ->
      $('#search-form').fadeIn()
    return false
