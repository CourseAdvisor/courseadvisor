<?php
class StaticController extends BaseController {
	public function faq() {
		return "coucou";
	}

    public function homepage()
    {
        return View::make('homepage');
    }

}
