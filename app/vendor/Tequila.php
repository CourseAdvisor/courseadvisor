<?php
namespace Tequila;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class Tequila {

	private $app;

	private $appName;
	private $redirectUrl;
	private $wantedAttributes = [];
	private $serverUrl;
	private $key;
	private $attributes = [];
	private $cookieName;

	public function __construct($app) {
		$this->cookieName = Config::get('tequila.cookie_name');

		if(Cookie::get($this->cookieName)) {
			$this->key = Cookie::get($this->cookieName);
		}

	}

	public function setApplicationName($appName) {
		$this->appName = $appName;
	}

	public function setWantedAttributes($attributes) {
		$this->wantedAttributes = $attributes;
	}

	public function setLoginRedirectUrl($url) {
		$this->redirectUrl = $url;
	}

	public function setServerUrl($url) {
		$this->serverUrl = $url;
	}

	public function get($attr) {
		return Session::get('tequila.' . $attr, false);
	}

	public function isLoggedIn() {
		return Session::get('tequila.logged_in', 0) == 1;
	}

	public function login($next = false) {
		if($next !== false) {
			Session::put('login.next', $next);
		}

		$params = [
			'urlaccess' => $this->redirectUrl,
			'service'	=> $this->appName,
			'request'	=> implode(',', $this->wantedAttributes)
		];

		$response = $this->askTequila('createrequest', $params);

		// check if response is false or does not contain key
		if(!$response) {
			return App::abort(500, "Unable to contact tequila server");
		}
		if (strpos($response, 'key=') != 0) {
			return App::abort(500, "Invalid response from tequila");
		}

		$this->setKey(explode('=', $response)[1]);
		return Redirect::to($this->serverUrl . 'requestauth?requestkey='.$this->key);
	}

	public function logout() {
		$this->destroySession();
		//Cookie::forget($this->cookieName);
		Cookie::queue($this->cookieName, '', -1);

		$next = Input::has('next') ? '?urlaccess=' . Input::get('next') : '';
		return Redirect::to($this->serverUrl . 'logout' . $next);
	}

	public function setKey($key) {
		Cookie::queue($this->cookieName, $key, Config::get('tequila.cookie_lifetime'));
		$this->key = $key;
	}

	// populates attributes from tequila (does NOT handle session stuff)
	public function fetchAttributes($key = false) {
		$key = $key !== false ? $key : $this->key;
		$response = $this->askTequila('fetchattributes', ['key' => $key]);

		if($response == false) {
			return false;
		}

		$attributes = explode("\n", $response);

		foreach($attributes as $attr) {
			if(empty($attr) || strpos($attr, '=') < 0) {
				continue;
			}

			list($name, $value) = explode('=', $attr);
			$this->attributes[$name] = $value;
		}

		// Check we had all required attributes
		foreach($this->wantedAttributes as $requiredAttribute) {
			if(!isset($this->attributes[$requiredAttribute])) {
				return false;
			}
		}
		return true;
	}

	// called when the user has logged in on tequila and is redirected back
	public function loginRedirect() {
		if(!Input::has('key')) {
			return App::abort(403, "No key provided");
		}

		$result = $this->fetchAttributes(Input::get('key'));

		if(!$result) {
			$this->destroySession();
			Cookie::forget($this->cookieName);
			return App::abort(500, "Something went wrong with tequila");
		}

		if(!isset($this->attributes['key'])) {
			return App::abort(500, "Tequila did not send back any key");
		}

		if($this->attributes['key'] != Input::get('key')) {
			return App::abort(403, "Token received from tequila does not match url");
		}

		$this->buildSession();
		Session::put('tequila.logged_in', 1);

		return true;


	}

	// Build session from attributes
	protected function buildSession() {
		foreach($this->attributes as $attr => $value) {
			Session::put('tequila.' . $attr, $value);
		}
	}

	protected function destroySession() {
		Session::forget('tequila');
	}

	protected function askTequila($what, $fields = []) {
		$allowedActions = ['createrequest', 'fetchattributes', 'logout'];

		if(!in_array($what, $allowedActions)) {
			throw new Exception("Invalid tequila request");
		}

		$ch = curl_init ();

	    curl_setopt ($ch, CURLOPT_HEADER,         false);
	    curl_setopt ($ch, CURLOPT_POST,           true);
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);

	    $url = $this->serverUrl . $what;
     	curl_setopt ($ch, CURLOPT_URL, $url);

 	    /* If fields where passed as parameters, */
 	    if (is_array ($fields) && count ($fields)) {
 	    	$pFields = array ();
 	      	foreach ($fields as $key => $val) {
 				$pFields[] = sprintf('%s=%s', $key, $val);
 	      	}
 	      	$query = implode("\n", $pFields) . "\n";
 	      	curl_setopt ($ch, CURLOPT_POSTFIELDS, $query);
 	    }
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    	'Content-Type: text/plain'
	    ));

 	    $response = curl_exec ($ch);

 	    if (curl_getinfo ($ch, CURLINFO_HTTP_CODE) != 200) {
 	      $response = false;
 	    }
 	    curl_close ($ch);
 	    return $response;
	}
}

