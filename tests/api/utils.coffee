frisby = require 'frisby'
request = require 'request'
Promise = require 'promise'
shared = require '../utils-shared.coffee'

# === frisby-only utilities ===
{fail} = utils = module.exports =
  test: (desc) -> new TestCase(desc)
  fail: (reason) -> expect("Test aborted. Reason: #{reason}").toBeUndefined()

  logged_in_as: (user, cb) ->
    # Subsequent request keep the session state
    myRequest = request.defaults jar: request.jar()
    # promise-ify request library
    getRequest = Promise.denodeify myRequest
    postRequest = Promise.denodeify myRequest.post

    # Performs the actual login.
    # param: cb(err) called on completion with a null error if success
    logIn = (cb) ->
      getRequest url: utils.url('/en/login'), followRedirect: false
      .then (res) ->
        if (res.statusCode == 307 || res.statusCode == 302)
          location = res.headers['location']
          requestKey = location.split('=')[1];
          base = location.substr(0, location.lastIndexOf('/'))
          postRequest url: "#{base}/login", followRedirect: false, form:
            requestkey: requestKey
            username: user
            password: user
        else
          throw Error("Error while requesting login page. Status is #{res.statusCode}")
      .then (res) -> getRequest url: res.headers['location']
      .then (res) -> cb(null)
      .catch (err) -> cb(err)

    # runs/waitsFor mechanism to tell jasmine to keep the test suite alive while we log in
    done = false
    runs -> logIn (err) ->
      if err then fail err
      else cb((desc) -> new TestCase(desc, request: myRequest))
      done = true
    waitsFor (-> done), "tequila authentication"

###
  Simple wrapper around frisby.

  Use mostly like frisby. Do this:
  ```
  test "Message"
  .on "url", method: 'post', otherSetting: value
  .is
  # frisby stuff
  .toss()
  ```
  instead of that:
  ```
  frisby.create "Message"
  .post "url", otherSetting: value
  # frisby stuff
  .toss()
  ```
###
class TestCase
  constructor: (desc, {@request = undefined} = {}) ->
    @is = frisby.create desc

  on: (url, params = {}) ->
    {method = 'GET', data = null} = params
    if params.mock? then return fail("Use constructor request param instead of mock")
    params.mock = @request
    method = method.toUpperCase()
    url = utils.url url
    @is._request.apply @is, [method].concat([url, data, params])
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
