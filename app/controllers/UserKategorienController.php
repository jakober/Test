<?php

class UserKategorienController extends BaseController {

    public function __construct() {
        parent::__construct();
        if ($this->user == null) {
            if (Session::has('newuser')) {
                $this->user = Session::get('newuser');
            }
        }
    }

    public function kategorien() {

        if ($this->user == null) {
            return Redirect::to('/');
        }
        $k = Kategorie::where('mandant_id', '=', $this->user->mandant_id)->get();
        $kats = array();
        foreach ($k as $kat) {
            $kats[] = array('i' => $kat->id, 'p' => $kat->hauptkategorie, 'n' => $kat->bezeichnung);
        }

        $k2 = DB::table('user_kategorien')->where('user_id', '=', $this->user->id)
                        ->select(array('kategorie_id', 'aktiv'))->get();

        $mykats = array();
        foreach ($k2 as $kat) {
            $mykats[] = array('i' => intval($kat->kategorie_id), 'a' => intval($kat->aktiv));
        }

        return View::make('user/kategorien')
                        ->with('kats', $kats)
                        ->with('mykats', $mykats)
                        ->with('isNewUser', Session::has('newuser'));
    }


	public function iframe(){
		
		if ($this->user == null) {
            return Redirect::to('/');
        }
		$k = Kategorie::where('mandant_id', '=', $this->user->mandant_id)->get();

        $kats = array();
        foreach ($k as $kat) {
            $kats[] = array('i' => $kat->id, 'p' => $kat->hauptkategorie, 'n' => $kat->bezeichnung);
        }
		$k2 = DB::table('user_kategorien')
		->leftJoin('kategorien', 'user_kategorien.kategorie_id', '=', 'kategorien.id')
        ->where('user_kategorien.user_id', '=', $this->user->id)
        ->select(array('user_kategorien.kategorie_id', 'user_kategorien.aktiv', 'kategorien.bezeichnung'))
        ->get();

        $mykats = array();
        foreach ($k2 as $kat) {
            $mykats[] = array('i' => intval($kat->kategorie_id), 'a' => intval($kat->aktiv), 'b' => $kat->bezeichnung);
        }
		
		return View::make('user/iframe')->with('kats', $kats)->with('mykats', $mykats);
	}
	
	public function iframeAusgabe($frameid,$key){

		$k2 = DB::table('iframe')
        ->where('id', $frameid)
		->where('iframekey', '=', $key)
        ->first();
		
	
		// $queries = DB::getQueryLog();
		// $last_query = end($queries);
		// print_r($last_query);
		// exit;
		
		$kats = unserialize($k2->kats);
		if(!is_array($kats)){
			$kats = [];
			return View::make('user/iframeAusgabe')->with('beitraege',0);
		}else{
			
		$b = DB::table('beitraege')
        ->whereIn('kategorie_id', $kats)
		->where('status_id', '>=', 5)
		->orderBy('id', 'desc')
        ->take($k2->anzahl)
        ->get();
		
		return View::make('user/iframeAusgabe')->with('beitraege',$b)->with('teaser_length',$k2->teaser_length)->with('linkcolor',$k2->linkcolor)->with('bgcolor',$k2->bgcolor)->with('padding',$k2->padding)->with('iframeid',$k2->id)->with('fontcolor',$k2->fontcolor);
				
		}
	}
	
	
	
	public function iframeAusgabeSingle($id,$iframeid){
		$k2 = DB::table('iframe')
        ->where('id', $iframeid)
        ->first();

		$beitrag = DB::table('beitraege')->where('id', $id)->first();
		
		return View::make('user/iframeAusgabeSingle')->with('beitrag',$beitrag)->with('linkcolor',$k2->linkcolor)->with('bgcolor',$k2->bgcolor)->with('padding',$k2->padding)->with('fontcolor',$k2->fontcolor);
				
		
	}


    public function neueKategorienHinzufuegen() {
        $ids = Input::get('ids');
        $user = $this->user;

        $r = array();
        if (is_array($ids)) {
            foreach ($ids as $id) {
                try {
                    UserKategorien::create(array(
                        'user_id' => $user->id,
                        'kategorie_id' => $id
                    ));
                    $r[$id] = 1;
                } catch (Exception $e) {
                    $r[$id] = 0;
                }
            }
        }
        return $r;
    }

    public function kategorieEntfernen() {
        $id = Input::get('id');
        try {
            UserKategorien::where('user_id', '=', $this->user->id)->where('kategorie_id', '=', $id)->delete();
        } catch (Exception $e) {
            return array('err' => 1);
        }
        return array();
    }
    
    
    public function beantragen() {
        $user = $this->user;
        $ids = Input::get('kats');
        if (is_array($ids)) {
            foreach ($ids as $id) {
                try {
                    UserKategorien::create(array(
                        'user_id' => $user->id,
                        'kategorie_id' => $id
                    ));
                } catch (Exception $e) {
                    
                }
            }
        }
        
    }

    public function finish() {
        $user = Session::get('newuser');
        
        $user->save();
        $key = Aktivierungsschluessel::create(array(
                    'id' => $user->id,
                    'key' => md5(uniqid())
        ));

        $ids = Input::get('kats');
        if (is_array($ids)) {
            foreach ($ids as $id) {
                try {
                    UserKategorien::create(array(
                        'user_id' => $user->id,
                        'kategorie_id' => $id
                    ));
                } catch (Exception $e) {
                    
                }
            }
        }
        $mandant = $user->mandant()->first();
        $data = array('user' => $user, 'mandant' => $mandant, 'key' => $key);
        Mail::send('emails/registrierung', $data, function($message) use($user, $mandant) {
                    $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
                    $message->to($user->email, $user->name)->subject('Registrierung zum ' . $mandant->bezeichnung);
                });
        return array();
    }

}
?>
