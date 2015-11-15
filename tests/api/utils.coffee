frisby = require 'frisby'
request = require 'request'
Q = require 'q'
shared = require '../utils-shared.coffee'

Q.longStackSupport = true

{fail} = utils = module.exports =
  fail: (reason) -> expect("Test aborted. Reason: #{reason}").toBeUndefined()
  test: (desc) -> new TestCaseBuilder({_desc: desc}, ROOT_BUILDER)

shared.extend(utils, shared)


class Session
  constructor: ->
    @request = request.defaults jar: request.jar()
    @get = Q.denodeify @request
    @post = Q.denodeify @request.post
    @csrf = null


SessionStore =
  # Returns a promise holding a Session object for the test's parameters
  # TODO: cache sessions
  getSessionForTestCase: (test) ->
    params = test.getParams()
    if !params.user?
      Q.Promise (resolve) => resolve(@getDefaultSession())
    else if @hasUserSession(params.user)
      Q.Promise (resolve) => resolve(@getUserSession(params.user))
    else
      session = new Session()
      Q.Promise (resolve) -> resolve(null)
      .then => @_authenticate(session, params.user) if params.user?
      .then => @_getCSRF(session)
      .then => @_store[params.user] = session


  getDefaultSession: ->
    Q.Promise (resolve) => resolve(@_defaultSession)
    .then (session) =>
      if !session?
        session = new Session()
        @_getCSRF(session).then -> session
      else
        session
    .then (session) => @_defaultSession = session

  hasUserSession: (user) -> @_store[user]?
  getUserSession: (user) -> @_store[user]

  _defaultSession: null
  _store: {}

  _getCSRF: (session) ->
    session.get url: utils.url('/api/csrf_token')
      .then ([res]) ->
        if res.statusCode != 200 then throw Error("Bad response from /api/csrf_token. Status: #{res.statusCode}")
        if !(matches = res.body.match(/TOKEN = (\w+)/i))? then throw Error("Bad response from /api/csrf_token. Body: #{res.body}")
        session.csrf = matches[1]

  _authenticate: (session, user) ->
    session.get url: utils.url('/en/login'), followRedirect: false
      .then ([res]) ->
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
      .then ([res]) ->
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
  constructor: ({@_useAJAX, @_user, @_url, @_desc, @_reqParams = {}, @_useCSRF}, @_parent) ->
  withUser: (@_user) -> @
  withAJAX: -> new TestCaseBuilder({_useAJAX: true}, @)
  withCSRF: -> new TestCaseBuilder({_useCSRF: true}, @)
  on: (url, reqParams) ->
    if typeof url != 'string'
      reqParams = url
      for verb in ['post', 'get', 'update', 'delete', 'patch', 'put']
        if reqParams[verb]?
          url = reqParams[verb]
          reqParams.method = verb
          delete reqParams[verb]
    @_url = url
    @_reqParams = reqParams || {}
    @
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
