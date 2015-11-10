frisby = require 'frisby'
request = require 'request'
Promise = require 'promise'
shared = require '../utils-shared.coffee'

# === frisby-only utilities ===
{fail} = utils = module.exports =
  test: (desc) -> new TestCase(desc)
  fail: (reason) -> expect("Test aborted. Reason: #{reason}").toBeUndefined()

#TODO: csrf, then refactor & cache types of sessions
class TestCase
  constructor: (desc, {@request = undefined} = {}) ->
    @_user = null
    @_desc = desc
    @rq = null

  withUser: (@_user) -> @

  on: (@_url, @_params = {}) -> @

  is: (cb) ->
    describe "Tequila auth", =>
      it "should authenticate with tequila", =>
        done = false
        runs =>
          Promise.all([
            if (@_user) then @_authenticate() else Promise.resolve(null),
            Promise.resolve(null) # TODO: CSRF
          ])
          .then => @_prepareRq()
          .then => cb(@rq)
          .then => @rq.toss()
          .catch (err) -> fail(err)
          .done -> done = true
        waitsFor (-> done), "tequila authentication"


  _prepareRq: () ->
    if @rq? then throw Error("Test case was launched twice !")
    @rq = frisby.create @_desc
    {method = 'GET', data = null} = @_params
    if @_params.mock? then return fail("Use constructor request param instead of mock")
    @_params.mock = @request
    method = method.toUpperCase()
    url = utils.url @_url
    @rq._request.apply @rq, [method].concat([url, data, @_params])
    @

  _authenticate: ->
    # Subsequent request keep the session state
    myRequest = request.defaults jar: request.jar()
    @request = myRequest
    # promise-ify request library
    getRequest = Promise.denodeify myRequest
    postRequest = Promise.denodeify myRequest.post

    getRequest url: utils.url('/en/login'), followRedirect: false
      .then (res) =>
        if (res.statusCode == 307 || res.statusCode == 302)
          location = res.headers['location']
          requestKey = location.split('=')[1];
          base = location.substr(0, location.lastIndexOf('/'))
          postRequest url: "#{base}/login", followRedirect: false, form:
            requestkey: requestKey
            username: @_user
            password: @_user
        else
          throw Error("Error while requesting login page. Status is #{res.statusCode}")
      .then (res) ->
        if (res.statusCode == 307 || res.statusCode == 302)
          getRequest url: res.headers['location']
        else
          throw Error("Invalid status code: #{res.statusCode} on /login response")

# extend shared utilities
for i in Object.keys(shared)
  utils[i] = shared[i]

# Do some setup by the way
frisby.globalSetup
  request:
    headers:
      "content-type": "application/json"
    timeout: 30000
