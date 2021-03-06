modals = require('./modals')

$ ->

  # Remember if we hid a comment body such that we don't forget to show it back
  hiddenBody = null
  # All comment forms
  allForms = $("[data-comment-form]")

  # restores the initial state
  closeAll = () ->
    allForms.slideUp()
    if (hiddenBody != null)
      hiddenBody.show()
      hiddenBody = null


  # use direct css property instead of the css class (which features an !important hack)
  allForms.filter(".hidden").hide().removeClass("hidden")
  # apply cancel button logic
  allForms.each ->
      $(@).find('[data-form-action=cancel]').click (evt) ->
        evt.preventDefault()
        closeAll()


  # Slides down the appropriate comment form to reply or edit.
  $("[data-comment-action]").click (evt) ->
    evt.preventDefault()
    closeAll()

    target = $(@).attr("data-comment-action")

    if (/^edit/.test(target))
      [..., id] = target.split(':')
      hiddenBody = $("[data-comment-body=#{id}]")
      hiddenBody.hide()

    $("[data-comment-form=\"#{target}\"").slideDown()

    # Now also check that the user is authenticated
    $.get('/api/is_auth')
      .fail (xhr) ->
        if (xhr.statusCode().status == 401) # Unauthorized
          modals.show('login-to-comment')
          closeAll()
