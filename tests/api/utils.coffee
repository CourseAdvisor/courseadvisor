frisby = require 'frisby'
request = require 'request'
shared = require '../utils-shared.coffee'

# === frisby-only utilities ===
{fail} = utils = module.exports =
  test: (desc) -> new TestCase(desc)
  fail: (reason) -> expect("Test aborted. Reason: #{reason}").toBeUndefined()

  logged_in_as: (user, cb) ->

    myRequest = request.defaults jar: true

    # todo use promises
    done = false
    runs ->
      myRequest url: utils.url('/en/login'), followRedirect: false, (err, res, body) ->
        if (err)
          fail(err)
        else if (res.statusCode == 307 || res.statusCode == 302)
          location = res.headers['location']
          requestKey = location.split('=')[1];
          base = location.substr(0, location.lastIndexOf('/'))
          myRequest.post url: "#{base}/login", followRedirect: false, form:
              requestkey: requestKey
              username: user
              password: user
            , (err, res, body) ->
              myRequest url: res.headers['location'], (err, res, body) ->
                console.log(res.statusCode)
                cb((desc) -> new AuthenticatedTestCase(myRequest, desc))
                done = true
        else
          fail("Error while requesting login page. Status is #{res.statusCode}")


    waitsFor( (-> done), "should have logged in")


class TestCase
  constructor: (@request, desc) ->
    @_r = frisby.create desc

  on: (url, params = {}) ->
    {method = 'GET', data = null} = params
    method = method.toUpperCase()
    url = utils.url url
    @_r._request.apply @_r, [method].concat([url, data, params])
    @

  is: (cb) ->
    cb(@_r)
    @

  toss: -> @_r.toss()


class AuthenticatedTestCase extends TestCase

  on: (url, params = {}) ->
    {method = 'GET', data = null} = params
    if params.mock? then return fail("Authenticated test case does not support mock param")
    params.mock = @request
    method = method.toUpperCase()
    url = utils.url url
    @_r._request.apply @_r, [method].concat([url, data, params])
    @


# extend shared utilities
for i in Object.keys(shared)
  utils[i] = shared[i]

# Do some setup by the way
frisby.globalSetup
  request:
    headers:
      "content-type": "application/json"
    timeout: 30000
