###
  layout.coffee

  js hacks to compensate for css's weaknesses
###


# mobile search navbar script
$ ->
  $('.mobile-search').each ->
    $el = $(this)
    initial_width = $el.css('width')
    padding = $el.css('padding-left')

    $input = $el.find('input')
      .focusin ->
        $el.css(opacity: '1', width: '100%')
      .focusout ->
        $el.css(opacity: '0', width: initial_width)
      .mouseup ->
        $input.select()
