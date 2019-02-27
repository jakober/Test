<?php

class HomeController extends BaseController {
    /*
      |--------------------------------------------------------------------------
      | Default Home Controller
      |--------------------------------------------------------------------------
      |
      | You may wish to use controllers instead of, or in addition to, Closure
      | based routes. That's great! Here is an example controller method to
      | get you started. To route to this controller, just add the route:
      |
      |	Route::get('/', 'HomeController@showWelcome');
      |
     */

    public function startseite() {

        if(isset($this->user)) {
            if($this->user->gruppe_id==2) {
                return Redirect::to('/rathaus/uebersicht');
            }
            return Redirect::to('/uebersicht');
        }
        return View::make('home/index');
    }

    public function impressum() {
        return View::make('home/impressum');
    }

}