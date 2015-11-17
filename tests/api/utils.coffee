###
  api/utils.coffee

  Utilities framework for API testing. This file must be required by all test files.

###


# Dependencies
frisby = require 'frisby'
request = require 'request'
Q = require 'q'
shared = require '../utils-shared.coffee'


# Sets up global parameters
Q.longStackSupport = true
frisby.globalSetup
  request:
    headers: {}
    timeout: 30000


# Entry point
{fail} = utils = module.exports =
  ###
  # Aborts the test case immediately for some `reason`
  ###
  fail: (reason) -> expect("Test aborted. Reason: #{reason}").toBeUndefined()
  ###
  # Starts a new test case definition.
  # see @TestCaseBuilder
  ###
  test: (desc) -> new TestCaseBuilder({_desc: desc}, ROOT_BUILDER)

# Extends common utilities (see ../utils-shared.coffee)
shared.extend(utils, shared)


###
# private
# Session represents an HTTP navigation session with persistent cookie jar.
#
# @request is a request object bound to the session. Use @get and @post for promisified
# get and post requests.
#
# see also:
# https://github.com/request/request
###
class Session
  constructor: ->
    # request object bound to the session
    @request = request.defaults jar: request.jar()
    # promise for get request on this session
    @get = Q.denodeify @request
    # promise for post request on this session
    @post = Q.denodeify @request.post
    # csrf token associated with this session
    @csrf = null


###
# private
# SessionStore is used for caching sessions.
# This saves the cost of fetching CSRF tokens and logging users in.
# Sessions are cached per user (seems to work with the way laravel generates csrf tokens).
###
SessionStore =
  # Returns a promise holding a Session object appropriate for the provided test
  # Concretely, sessions are bound to users.
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
  # Returns the session for unauthenticated navigation
  getDefaultSession: ->
    Q.Promise (resolve) => resolve(@_defaultSession)
    .then (session) =>
      if !session?
        session = new Session()
        @_getCSRF(session).then -> session
      else
        session
    .then (session) => @_defaultSession = session
  # Returns true if the store already holds a session for that user
  hasUserSession: (user) -> @_store[user]?
  # Returns the session associated to that user or undefined if it doesn't exist
  getUserSession: (user) -> @_store[user]
  # Actual store
  _defaultSession: null
  _store: {}
  # Returns a promise populating the session's csrf token.
  # session.csrf is set to the csrf token before the promise resolves but the
  # resolved value is undefined
  _getCSRF: (session) ->
    session.get url: utils.url('/api/csrf_token')
      .then ([res]) ->
        if res.statusCode != 200 then throw Error("Bad response from /api/csrf_token. Status: #{res.statusCode}")
        if !(matches = res.body.match(/TOKEN = (\w+)/i))? then throw Error("Bad response from /api/csrf_token. Body: #{res.body}")
        session.csrf = matches[1]
  # Authenticates the provided session with the provided user in a promise.
  # The promise's resolved value is undefined.
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


###
# Test case with fixed parameters. Ensures some boilerplate parameters
# for running frisby tests
###
class TestCase
  constructor: ({@_user, @_url, @_desc, @_reqParams, @_useCSRF, @_useAJAX}) ->
  # Executes cb passing a prepared frisby as only argument
  # The frisby can be used normally according to the official doc.
  # All boilerplate options specified in the test case are taken care of.
  # see http://frisbyjs.com/docs/api/ and https://github.com/vlucas/frisby/blob/master/lib/frisby.js
  # for complete documentation.
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
  # Returns cleaned hash of this test case's parameters
  getParams: ->
    url: @_url
    desc: @_desc
    user: @_user
    csrf: @_useCSRF
    ajax: @_useAJAX
    reqParams: @_reqParams
  # Returns a human readable string of this test case's parameters
  toString: -> "TestCase: " + JSON.stringify @getParams()
  # Prepares a request for use in this test case
  _prepareRq: (session) =>
    # shallow copy parameters
    params = utils.extend({}, @_reqParams)
    # Expand url
    url = utils.url @_url

    rq = frisby.create @_desc
    # extract interesting parameters
    {method = 'GET', body = {}} = params
    delete params.body
    # Inject session request for use by frisby `rq`
    if params.mock? then return fail("Cannot use the mock param.")
    params.mock = session.request
    # Inject CSRF body parameter
    if @_useCSRF then body._token = session.csrf
    # Perform actual request
    method = method.toUpperCase()
    rq._request.apply rq, [method].concat([url, body, params])
    # Inject AJAX header to fake AJAX
    if (@_useAJAX) then rq.addHeaders "X-Requested-With": "XMLHttpRequest"
    return rq


###
# Builder used to prepare a frisbyjs test case with the proper session attributes.
#
# Use .on once to specify the HTTP request to test. Optionnaly use .withXXX methods
# to add more properties to the request.
###
class TestCaseBuilder
  constructor: ({@_useAJAX, @_user, @_url, @_desc, @_reqParams = {}, @_useCSRF}, @_parent) ->
  # Perform the request as logged in user user
  withUser: (@_user) -> @
  # Simulates an AJAX request
  withAJAX: -> new TestCaseBuilder({_useAJAX: true}, @)
  # Provides the correct CSRF token in POST|PUT|PATCH|UPDATE request as "_token" parameter
  withCSRF: -> new TestCaseBuilder({_useCSRF: true}, @)
  # Specifies the target request for this test case. This method has two forms:
  # .on(url, reqParams)
  #   - url: The target url
  #   - reqParams: options passed to the request library
  # .on( verb: url, reqParams)
  #   Shorthand for .on(url, {method: verb} U reqParams)
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
  # Returns the test case built with the current parameters
  build: ->
    @_inheritProperties()
    new TestCase(@)
  # Executes cb in the context of the current TestCase
  # see: @TestCase.is
  is: (cb) ->
    tc = @build()
    tc.is(cb)
  _inheritProperties: ->
    if !@_parent? then return
    @_parent._inheritProperties()
    utils.extend(@_reqParams, @_parent._reqParams)
    utils.extend(@, @_parent)

# Default values for TestCaseBuilder
ROOT_BUILDER = new TestCaseBuilder(
  _useAJAX: false
  _user: null
  _url: null
  _desc: null
  _reqParams: {}
  _useCSRF: false
)
