module.exports =
  v: (msg) -> console.log(prepare(msg)) if (DEBUG? && DEBUG)
  e: (msg) -> console.error(prepare(msg)) if (DEBUG? && DEBUG)

prepare = (msg) ->
  if typeof(msg) == 'string' then msg
  else JSON.stringify(msg)
