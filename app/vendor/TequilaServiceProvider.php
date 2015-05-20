<?php namespace Tequila;

 use Illuminate\Support\ServiceProvider;
 use Illuminate\Support\Facades\Config;

 class TequilaServiceProvider extends ServiceProvider {

 	public function register() {
 		/*$this->app->singleton('Tequila\TequilaAuth', function($app) {
 			return new TequilaAuth($app);
 		});*/
		$this->app['tequila'] = $this->app->share(function ($app) {
			$auth = new Tequila($app);
			$auth->setApplicationName('CourseAdvisor');
			$auth->setLoginRedirectUrl(Config::get('tequila.redirect_url'));
			$auth->setServerUrl(Config::get('tequila.server_url'));
			$auth->setWantedAttributes(['uniqueid','name','firstname','unit', 'unitid', 'where', 'group', 'email']);
			return $auth;
		});
 	}

 	public function boot() {

 	}

 	public function provides() {
 		return ['Tequila\Tequila'];
 	}
 }