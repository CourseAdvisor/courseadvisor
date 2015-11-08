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

      $.post '/api/vote',
        type: type # up / down
        "#{target}": id
        _token: TOKEN
      .done (resp) ->
        data = JSON.parse(resp)
        if (!data.cancelled)
          $el.addClass('voted');

        $score.queue (next) ->
          $score.text(data.score)
          next()

      .fail (xhr) ->
        if (xhr.statusCode().status == 401) # Unauthorized
          modals.show('login-to-vote')

      .always ->
        $score.animate(opacity: 1)
        _votes.pending = false
