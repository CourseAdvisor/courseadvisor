modals = require('./modals')
events = require('./events')
log = require('./log')

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

      params =
        type: type # up / down
        "#{target}": id
        _token: TOKEN

      log.v("Voting with params: #{JSON.stringify(params)}")
      $.post '/api/vote', params
      .done (resp) ->
        data = JSON.parse(resp)
        if (!data.cancelled)
          $el.addClass('voted')

        log.v("Voting succeded")
        $score.queue (next) ->
          $score.text(data.score)
          next()

      .fail (xhr) ->
        if (xhr.statusCode().status == 401) # Unauthorized
          log.e("Voting failed. Reason: Unauthorized")
          modals.show('login-to-vote')
        else
          log.e("Voting failed. #{xhr.statusCode().status} ; #{xhr.responseText}")

      .always ->
        log.v("Voting finished")
        $score.animate(opacity: 1)
        events.trigger('vote.completed')
