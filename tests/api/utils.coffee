frisby = require 'frisby'
request = require 'request'
Promise = require 'promise'
shared = require '../utils-shared.coffee'


{fail} = utils = module.exports =
  fail: (reason) -> expect("Test aborted. Reason: #{reason}").toBeUndefined()
  test: (desc) -> new TestCaseBuilder({_desc: desc}, ROOT_BUILDER)

shared.extend(utils, shared)


class Session
  constructor: ->
    @request = request.defaults jar: request.jar()
    @get = Promise.denodeify @request
    @post = Promise.denodeify @request.post
    @csrf = null


SessionStore =
  # Returns a promise holding a Session object for the test's parameters
  # TODO: cache sessions
  getSessionForTestCase: (test) ->
    params = test.getParams()
    session = new Session()
    Promise.all([
        if params.csrf then @_getCSRF(session) else null,
        if params.user? then @_authenticate(session, params.user) else null
      ])
      .then -> session

  _getCSRF: (session) ->
    session.get url: utils.url('/api/csrf_token')
      .then (res) ->
        if res.statusCode != 200 then throw Error("Bad response from /api/csrf_token. Status: #{res.statusCode}")
        if !(matches = res.body.match(/TOKEN = (\w+)/i))? then throw Error("Bad response from /api/csrf_token. Body: #{res.body}")
        session.csrf = matches[1]

  _authenticate: (session, user) ->
    session.get url: utils.url('/en/login'), followRedirect: false
      .then (res) ->
        if (res.statusCode == 307 || res.statusCode == 302)
          location = res.headers['location']
          requestKey = location.split('=')[1];
          base = location.substr(0, location.lastIndexOf('/'))
          session.post url: "#{base}/login", followRedirect: false, form:
            requestkey: requestKey
            username: user
            password: user
        else
          throw Error("Error while requesting login page. Status is #{res.statusCode}")
      .then (res) ->
        if (res.statusCode == 307 || res.statusCode == 302)
          session.get url: res.headers['location']
        else
          throw Error("Invalid status code: #{res.statusCode} on /login response")


class TestCase
  constructor: ({@_user, @_url, @_desc, @_reqParams, @_useCSRF, @_useAJAX}) ->
  is: (cb) ->
    describe "test case", =>
      it "should prepare #{@}", =>
        done = false
        runs =>
          SessionStore.getSessionForTestCase @
            .then @_prepareRq
            .then (rq) =>
              cb(rq)
              return rq
            .then (rq) => rq.toss()
            .catch (err) -> fail("#{err} #{err.stack}")
            .done -> done = true
        waitsFor (-> done), "test case completion"
    @
  getParams: ->
    url: @_url
    desc: @_desc
    user: @_user
    csrf: @_useCSRF
    ajax: @_useAJAX
    reqParams: @_reqParams
  toString: -> "TestCase: " + JSON.stringify @getParams()
  _prepareRq: (session) =>
    rq = frisby.create @_desc
    {method = 'GET', data = {}} = @_reqParams
    if @_reqParams.mock? then return fail("Use constructor request param instead of mock")
    @_reqParams.mock = session.request
    if @_useCSRF then data._token = session.csrf
    method = method.toUpperCase()
    url = utils.url @_url
    rq._request.apply rq, [method].concat([url, data, @_reqParams])
    if (@_useAJAX) then rq.addHeaders "X-Requested-With": "XMLHttpRequest"
    return rq


class TestCaseBuilder
  constructor: ({@_useAJAX, @_user, @_url, @_desc, @_reqParams, @_useCSRF}, @_parent) ->
    utils.extend(@, @_parent)
    @_reqParams = {}
    @withCSRF = if @_useCSRF then @ else new TestCaseBuilder({_useCSRF: true}, @)
    @withAJAX = if @_useAJAX then @ else new TestCaseBuilder({_useAJAX: true}, @)
  withUser: (@_user) -> @
  on: (@_url, @_reqParams = {}) -> @
  build: ->
    @_inheritProperties()
    new TestCase(@)
  is: (cb) ->
    tc = @build()
    tc.is(cb)
  _inheritProperties: ->
    if !@_parent? then return
    @_parent._inheritProperties()
    utils.extend(@_reqParams, @_parent._reqParams)
    utils.extend(@, @_parent)


ROOT_BUILDER = new TestCaseBuilder(
  _useAJAX: false
  _user: null
  _url: null
  _desc: null
  _reqParams: {}
  _useCSRF: false
)


# Do some setup by the way
frisby.globalSetup
  request:
    headers: {}
    timeout: 30000
