<?php

class BaseController extends Controller {

	public function __construct()
	{
		Breadcrumbs::addCrumb('Home', '/');
		Breadcrumbs::setCssClasses('breadcrumb');
		Breadcrumbs::setDivider('');
	}

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

	/**
	 *	Adds a breadcrumb to the breadcrumb stream.
	 *	**name is unsafe (not escaped)**
	 *
	 * @param $action controller action this breadcrumb links to
	 * @param $name (unsafe) text shown in the breadcrumb link
	 * @param $params (optionnal) action parameters to build the link
	 *
	 * @return void
	 */
	public function addCrumb($action, $name, $params = null) {
		Breadcrumbs::addCrumb(HTML::linkAction($action, $name, $params), action($action, $params));
	}

}
