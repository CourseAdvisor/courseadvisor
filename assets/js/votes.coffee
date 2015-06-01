
$ -> # jQuery onLoad
  $('[data-vote-btn]').each ->
    $el = $(@)
    [type, id] = $el.attr('data-vote-btn').split(':')

    $score = $("[data-vote-score=#{id}]")

    $btns = $("[data-vote-btn$=#{id}]")

    $el.click (evt) ->
      evt.preventDefault()
      $score.animate(opacity: 0)
      $btns.removeClass('voted');

      $.post '/api/vote',
        type: type # up / down
        review: id
        _token: TOKEN
      .done (resp) ->
        $el.addClass('voted')
        data = JSON.parse(resp)
        $score.queue (next) ->
          $score.text(data.score)
          next()

      .fail (xhr) ->
        if (xhr.statusCode().status == 401) # Unauthorized
          $('#login-to-vote-modal').modal('show')

      .always ->
        $score.animate(opacity: 1)



