
$ ->

  allForms = $("[data-comment-form]")

  allForms
    # use direct css property instead of the css class (which features an !important hack)
    .hide().removeClass("hidden")

    .each ->
      $el = $(@)
      $el.find('[data-form-action=cancel]').click (evt) ->
        evt.preventDefault()
        $el.slideUp()


  $("[data-comment-action]").click (evt) ->
    evt.preventDefault()

    $el = $(@)
    target = $el.attr("data-comment-action")

    allForms.slideUp()
    $("[data-comment-form=\"#{target}\"").slideDown()