<?php namespace Helpers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Url;

class MenuHelper {

	const ACTIVE_CLASS_STR = 'class="active" ';
	private static $activeFound = false;

	public static function active_if_controller($controllerName) {
		if(self::$activeFound) {
			return;
		}
		$currentController = explode('@', Route::getCurrentRoute()->getAction()['controller'])[0];
		$pos = strpos($currentController, "Controller");
		$cleanedName = substr($currentController, 0, $pos);
		if(strcasecmp($cleanedName, $controllerName) == 0) {
			self::$activeFound	= true;
			echo self::ACTIVE_CLASS_STR;
		}
	}

	public static function active_if_home() {
		if(self::$activeFound) {
			return;
		}
		$fullUrl = Request::url();
		$baseUrl = URL::to('/');
		
		if($fullUrl == $baseUrl || $fullUrl == $baseUrl . "/") {
			self::$activeFound	= true;
			echo self::ACTIVE_CLASS_STR;
		}
	}
}