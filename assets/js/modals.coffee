

modal = do ->
  $el = null
  $texts = null

  init = ->
    $el = $("#base-modal")
    $texts = $el.find("[data-modal-text]")

  return {
    show: (which) ->
      init() if (!$el)
      $texts.hide().filter("[data-modal-text=\"#{which}\"]").show()
      $el.modal('show')
  }

module.exports = modal
