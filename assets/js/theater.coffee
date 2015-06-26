
###
  Shows images in theater mode when clicked.

  usage: wrap the <img> in a <a data-theater="fullscreen"> element
###
$ ->

  # inject html
  $backdrop = $('<div class="backdrop" />').appendTo('body').hide()
  $img = $('<img class="theater-img"/>').appendTo($backdrop).hide()

  # hide events
  $('body').keydown (evt) -> hide() if (evt.which == 27) # ESC
  $backdrop.click -> hide()

  $img.load ->
    $img.css
      left: ($(window).width() - $img.width())/2
      top: ($(window).height() - $img.height())/2

    $img.fadeIn()

  # instrument markup
  $('[data-theater=fullscreen]').each ->
    $el = $(@)

    $myImg = $el.children("img")

    $el.click (evt) ->
      evt.preventDefault();
      $img.attr('src', $myImg.attr('src'))
      $backdrop.fadeIn()
      $('body').addClass('modal-open')
      #$('body').css('overflow', 'hidden')

  hide = ->
    $backdrop.fadeOut().queue (next) ->
      $img.hide()
      next()
    $('body').css('overflow', 'visible')
    $('body').removeClass('modal-open')