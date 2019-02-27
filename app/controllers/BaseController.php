<?php

class BaseController extends Controller {

    public function __construct() {

        $this->mandant = Mandant::where('hostname','=',$_SERVER['HTTP_HOST'])->first();

        if (Session::has('user_id')) {
            $this->user = User::find(Session::get('user_id'));
            if($this->user==null) {
                Session::forget('user_id');
                Return Redirect::to('/');
            }
            $ausgabe = $this->mandant->ausgaben()->next(1)->first();

            View::share('naechsteAusgabe', $ausgabe);
        } else {
            $this->user = null;
        }
        View::share('user', $this->user);
        View::share('mandant', $this->mandant);
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout() {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }


}