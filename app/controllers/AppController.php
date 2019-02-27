<?php

class AppController extends BaseController {

    public function uebersicht() {
        if ($this->user) {
            $beitraege = $this->user->beitraege()->whereIn('status_id', array(1,2,3,4,5,6))->get();
            $meldungen = Beitragsprotokoll::where('recipient_user_id', '=', $this->user->id)->orderBy('created_at','desc')->take(10)->get();
            $katids = UserKategorien::where('user_id','=',$this->user->id)->where('aktiv','=',1)->select('kategorie_id')->get();
            $a = array();
            foreach($katids as $k) {
                $a[] = $k->kategorie_id;
            }
            $kats = array();

            if(count($a)>0) {
                $kats = Kategorie::whereIn('id',$a)->get();
            }
            return View::make('user/uebersicht')
                            ->with('kategorien', $kats)
                            ->with('beitraege', $beitraege)
                            ->with('meldungen', $meldungen);
        } else {
            return Redirect::to('/');
        }
    }

}