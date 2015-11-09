modals = require('./modals')

# used to help tests figure out when vote has been performed
window._votes =
  pending: false

$ -> # jQuery onLoad
  $('[data-vote-btn]').each ->
    $el = $(@)
    [type, target, id] = $el.attr('data-vote-btn').split(':')

    $score = $("[data-vote-score=\"#{target}:#{id}\"]")

    $btns = $("[data-vote-btn$=\"#{target}:#{id}\"]")

    $el.click (evt) ->
      evt.preventDefault()
      $score.animate(opacity: 0)
      $btns.removeClass('voted');
      _votes.pending = true

      params =
        type: type # up / down
        "#{target}": id
        _token: TOKEN

      console.log("Voting with params: #{JSON.stringify(params)}")
      $.post '/api/vote', params
      .done (resp) ->
        data = JSON.parse(resp)
        if (!data.cancelled)
          $el.addClass('voted')

        console.log("Voting succeded")
        $score.queue (next) ->
          $score.text(data.score)
          console.log("Score updated")
          next()

      .fail (xhr) ->
        if (xhr.statusCode().status == 401) # Unauthorized
          console.error("Voting failed. Reason: Unauthorized")
          modals.show('login-to-vote')
        else
          console.error("Voting failed. Reason: Unknown")
          console.error(xhr.statusCode().status)
          console.error(xhr.responseText)

      .always ->
        console.log("Voting finished")
        $score.animate(opacity: 1)
        _votes.pending = false
