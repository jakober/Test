<?php

class RathausController extends BaseController {

    public function uebersicht() {
        if ($this->user == null || $this->user->gruppe_id != 2) {
            return Redirect::to('uebersicht');
        }
        $mandant = Session::get('mandant_id');
        $newusers = User::where('mandant_id', '=', $mandant)->where('freigeschaltet', '=', 0)->where('aktiviert', '=', 1)->count();

        $katc = DB::table('users')
                ->join('user_kategorien', 'users.id', '=', 'user_kategorien.user_id')
                ->where('mandant_id', '=', $mandant)
                ->where('users.freigeschaltet', '=', 1)
                ->where('user_kategorien.aktiv', '=', 0)
                ->count();

        $ausgaben = Ausgabe::next($this->mandant->id, 6)->get();
        $count = 0;
        $list = array();
        foreach ($ausgaben as $a) {
            $c = DB::table('ausgaben_beitraege')
                    ->join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id')
                    ->where('beitraege.status_id', '=', '2')
                    ->where('ausgaben_beitraege.ausgabe_id', '=', $a->id)
                    ->count();
            if ($c > 0) {
                $o = new stdClass();
                $o->ausgabe = $a;
                $o->count = $c;
                $list[] = $o;
                $count+=$c;
            }
        }

        return View::make('/rathaus/uebersicht')
                        ->with('newusers', $newusers)
                        ->with('katc', $katc)
                        ->with('m', $mandant)
                        ->with('ausgaben_beitraege', $list)
                        ->with('beitraege_count', $count);
    }

    public function benutzerantraege() {
        if ($this->user == null || $this->user->gruppe_id != 2) {
            return Redirect::to('uebersicht');
        }

        $mandant = Session::get('mandant_id');
        $newusers = User::where('mandant_id', '=', $mandant)->where('freigeschaltet', '=', 0)->where('aktiviert', '=', 1)->get();

        /*
          SELECT users.id,COUNT(user_kategorien.user_id) FROM users
          LEFT JOIN user_kategorien
          ON users.id=user_kategorien.user_id
          WHERE mandant_id=1
          AND users.freigeschaltet=1
          AND user_kategorien.aktiv=0
          GROUP BY users.id
         */

        $res = DB::table('users')
                        ->join('user_kategorien', 'users.id', '=', 'user_kategorien.user_id')
                        ->where('mandant_id', '=', $mandant)
                        ->where('users.freigeschaltet', '=', 1)
                        ->where('user_kategorien.aktiv', '=', 0)
                        ->groupBy('users.id')->select('users.id')->get();

        $uids = array();
        foreach ($res as $o) {
            $uids[] = $o->id;
        }

        $users = count($uids) > 0 ? User::whereIn('id', $uids)->get() : array();
        return View::make('/rathaus/benutzerantraege')->with('newusers', $newusers)->with('m', $mandant)->with('users', $users);
    }

    public function beitraege() {
        if ($this->user == null || $this->user->gruppe_id < 2) {
            return Redirect::to('uebersicht');
        }

        $ausgaben = Ausgabe::nextB($this->user->mandant_id, 6)->get();
        return View::make('rathaus/beitraege')
                        ->with('ausgaben', $ausgaben);
    }

    public function benutzer() {
        $errors = array();

        if ($this->user == null || $this->user->gruppe_id != 2) {
            return Redirect::to('uebersicht');
        }

        $input = array('anrede' => 'm', 'email' => '', 'name' => '', 'errors' => array());
        $email = Input::get('email');
        if ($email !== null) {

            $validator = Validator::make(array(
                        'email' => $email,
                        'name' => Input::get('name'),
                        'anrede' => Input::get('anrede'),
                            ), array(
                        'name' => 'required',
                        'anrede' => 'required',
                        'email' => 'required|email'
                            ), array(
                        'name.required' => 'Der Name muss angegeben werden',
                        'email.required' => 'Die E-Mail-Adresse muss angeben werden',
                        'email.email' => 'Die E-Mail-Adresse ist ungültig',
                        'anrede.required' => 'Eine Anrede muss angegeben werden'
                            )
            );

            if ($validator->fails()) {
                $errors = $validator->messages()->all('<li>:message</li>');
            } else {
                $this->einladungVersenden(Input::get('anrede'), Input::get('name'), Input::get('email'));
/*
                $errors = array();
                $count = User::where('email', '=', $email)->where('mandant_id', '=', $this->mandant->id)->count();
                if ($count) {
                    $errors[] = '<li>Der Benutzer ist bereits registiert</li>';
                } else {
                    $count = Einladung::where('email', '=', $email)->where('mandant_id', '=', $this->mandant->id)->count();
                    if ($count) {
                        $errors[] = '<li>Es wurde bereits eine Einladung an die E-Mail-Addresse versendet</li>';
                    } else {
                        $this->einladungVersenden(Input::get('anrede'), Input::get('name'), Input::get('email'));
                    }
                }
 *
 */
            }
        }

        $input['users'] = User::where('mandant_id', '=', Session::get('mandant_id'))->where('freigeschaltet', '=', 1)->orderBy('gruppe_id')->orderBy('name')->orderBy('vorname')->orderBy('username')->get(); //;

        if (count($errors)) {
            $input['meldungen'] = '<ul>' . implode('', $errors) . '</ul>';
            $input['email'] = $email;
            $input['anrede'] = Input::get('anrede');
            $input['name'] = Input::get('name');
        } else {
            $input['meldungen'] = '';
        }
        return View::make('rathaus/benutzer', $input);
    }

    private function einladungVersenden($anrede, $name, $email) {
        $mandant = Mandant::find(Session::get('mandant_id'));
        $data = array(
            'anrede' => $anrede,
            'name' => $name,
            'email' => $email,
            'text' => file_get_contents(storage_path() . '/texte/email_' . $mandant->id . '.txt')
        );

        Einladung::create(array(
            'anrede' => $anrede,
            'name' => $name,
            'email' => $email,
            'mandant_id' => $this->mandant->id
        ));

        $mandant = Mandant::find(Session::get('mandant_id'));

        Mail::send('emails/einladung', $data, function($message) use($mandant, $email, $name) {
            $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
            $message->to($email, $name)->subject('Ihr Zugang zum Redaktionssystem ' . $mandant->bezeichnung);
        });
    }

    public function freigabe() {
        if ($this->user == null || $this->user->gruppe_id != 2) {
            return array();
        }
        $user_id = Input::get('id');
        $freigabe = Input::get('f');
        $k1 = Input::get('k1');
        if ($k1 == null) {
            $k1 = array();
        }
        $k1b = array();
        foreach ($k1 as $k) {
            $k1b[] = Kategorie::find($k)->bezeichnung;
        }
        $k0 = input::get('k0');
        if ($k0 == null) {
            $k0 = array();
        }
        $k0b = array();
        foreach ($k0 as $k) {
            $k0b[] = Kategorie::find($k)->bezeichnung;
        }

        $mandant = Mandant::find(Session::get('mandant_id'));
        $user = User::where('id', '=', $user_id)->first();
        $data = array('user' => $user, 'mandant' => $mandant, 'k0' => $k0b, 'k1' => $k1b);
        if ($freigabe == 1) {
            try {
                DB::transaction(function() use($user, $k0, $k1) {
                    $user->freigeschaltet = 1;
                    $user->save();
                    if (count($k0) > 0) {
                        UserKategorien::where('user_id', '=', $user->id)->whereIn('kategorie_id', $k0)->delete();
                    }
                    foreach ($k1 as $k) {
                        $uk = UserKategorien::where('user_id', '=', $user->id)->where('kategorie_id', '=', $k)->first();
                        $uk->aktiv = 1;
                        $uk->save();
                    }
                });
                Mail::send('emails/zugang_freigeschaltet', $data, function($message) use($user, $mandant) {
                    $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
                    $message->to($user->email, $user->name)->subject('Freischaltung Ihrer Registrierung für ' . $mandant->bezeichnung);
                });
            } catch (Exception $e) {
                //return array('err' => 1);
                throw $e;
            }
        } else {
            try {
                DB::transaction(function() use($user, $mandant, $data) {
                    //UserKategorien::where('user_id', '=', $user->id)->delete();
                    //$user->delete();
                    $user->freigeschaltet = 2;
                    $user->save();
                    Mail::send('emails/zugang_abgelehnt', $data, function($message) use($user, $mandant) {
                        $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
                        $message->to($user->email, $user->name)->subject('Ablehnung Ihrer Registrierung zu: ' . $mandant->bezeichnung);
                    });
                });
            } catch (Exception $e) {
                return array('err' => 1);
            }
        }
        return array();
    }

    public function kategorien_uebernehmen() {
        if ($this->user == null || $this->user->gruppe_id != 2) {
            return array();
        }
        $user_id = Input::get('id');
        $k1 = Input::get('k1');
        if ($k1 == null) {
            $k1 = array();
        }
        $k1b = array();
        foreach ($k1 as $k) {
            $k1b[] = Kategorie::find($k)->bezeichnung;
        }
        $k0 = input::get('k0');
        if ($k0 == null) {
            $k0 = array();
        }
        $k0b = array();
        foreach ($k0 as $k) {
            $k0b[] = Kategorie::find($k)->bezeichnung;
        }

        $mandant = Mandant::find(Session::get('mandant_id'));
        $user = User::where('id', '=', $user_id)->first();
        $data = array('user' => $user, 'mandant' => $mandant, 'k0' => $k0b, 'k1' => $k1b);
        try {
            DB::transaction(function() use($user, $k0, $k1) {
                $user->freigeschaltet = 1;
                $user->save();
                if (count($k0) > 0) {
                    UserKategorien::where('user_id', '=', $user->id)->whereIn('kategorie_id', $k0)->delete();
                }
                foreach ($k1 as $k) {
                    $uk = UserKategorien::where('user_id', '=', $user->id)->where('kategorie_id', '=', $k)->first();
                    $uk->aktiv = 1;
                    $uk->save();
                }
            });
            Mail::send('emails/kategorien_freigeschaltet', $data, function($message) use($user, $mandant) {
                $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
                $message->to($user->email, $user->name)->subject('Ihre Rubrikanträge bei ' . $mandant->bezeichnung . ' wurden bearbeitet.');
            });
        } catch (Exception $e) {
            //return array('err' => 1);
            throw $e;
        }
        return array();
    }

    public function beitrags_status() {
        if ($this->user == null || $this->user->gruppe_id != 2) {
            return array();
        }
        $status_id = Input::get('status');
        $id = Input::get('id');
        $beitrag = Beitrag::where('id', '=', $id)->first();
        $beitrag->status_id = $status_id;
        $beitrag->save();
        $status = Status::where('id', '=', $status_id)->first();
        $begruendung = trim(Input::get('begruendung'));
        if (intval($status_id) == 5) {
            $user = $beitrag->user()->first();
            $mandant = Mandant::find(Session::get('mandant_id'));

            Mail::send('emails/artikel_freigegeben', array('user' => $user, 'mandant' => $mandant, 'beitrag' => $beitrag), function($message) use($user, $mandant, $beitrag) {
                $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
                $message->to($user->email, $user->name)->subject('Freigabe des Artikels "' . $beitrag->ueberschrift . '"');
            });

            Beitragsprotokoll::create(array(
                'beitrag_id' => $beitrag->id,
                'recipient_user_id' => $beitrag->user_id,
                'action_user_id' => $this->user->id,
                'action_id' => 3,
                'fuer_rathaus' => true,
                'fuer_redaktion' => true
            ));
        } else if (intval($status_id) == 4) {
            $user = $beitrag->user()->first();
            $mandant = Mandant::find(Session::get('mandant_id'));

            Mail::send('emails/artikel_abgelehnt', array('user' => $user, 'mandant' => $mandant, 'beitrag' => $beitrag, 'begruendung' => $begruendung), function($message) use($user, $mandant, $beitrag) {
                $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
                $message->to($user->email, $user->name)->subject('Ablehnung des Artikels "' . $beitrag->ueberschrift . '"');
            });
            Beitragsprotokoll::create(array(
                'beitrag_id' => $beitrag->id,
                'recipient_user_id' => $beitrag->user_id,
                'action_user_id' => $this->user->id,
                'action_id' => 5,
                'fuer_rathaus' => true,
                'fuer_redaktion' => true,
                'nachricht' => $begruendung
            ));
        }

        return array('img' => $status->bild, 'text' => $status->bezeichnung, 'status' => $status_id);
    }

    public function ausgaben($jahr) {
        if ($this->user == null || $this->user->gruppe_id < 2) {
            return Redirect::to('uebersicht');
        }
        $tage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
        $ausgaben = Ausgabe::where('mandant_id', '=', $this->mandant->id)->where('jahr', '=', $jahr)->orderBy('erschdat')->get();
        
        $ausgabe_last = Ausgabe::where('mandant_id', '=', $this->mandant->id)->where('jahr', '=', $jahr)->orderBy('erschdat','desc')->first();
        
        return View::make('rathaus/ausgaben')
                        ->with('jahr', $jahr)
                        ->with('ausgaben', $ausgaben)
                        ->with('ausgabe_last',$ausgabe_last)
                        ->with('tage',$tage);
        }
    


      public function ausgaben_generieren(){
       if ($this->user == null || $this->user->gruppe_id < 2) {
            return Redirect::to('uebersicht');
        }
       
        // DB::table('users')->insert(
            // array('email' => 'john@example.com', 'votes' => 0)
        // );
       
       $jahr = '2018';
        
       
        $tage = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag","Sonntag");
        $ausgaben = Ausgabe::where('mandant_id', '=', $this->mandant->id)->orderBy('erschdat')->get();
        
        $ausgabe_last = Ausgabe::where('mandant_id', '=', $this->mandant->id)->orderBy('erschdat','desc')->first();
        
            
        return View::make('rathaus/ausgaben_generieren')
                        ->with('jahr', $jahr)
                        ->with('ausgaben', $ausgaben)
                        ->with('ausgabe_last',$ausgabe_last)
                        ->with('tage',$tage);
        }
    
    public function generateAusgabe(){
            
        $redaktionsschluss = $_POST["redaktionsschluss"];
        $erscheinungsdatum = $_POST["erscheinungsdatum"];
        $gerade = $_POST["gerade"];
        $ungerade = $_POST["gerade"];
        $anzahl = $_POST["anzahl"];
        $stunde = $_POST["stunde"];
        $minute = $_POST["minute"];
        $jahr = '2019';
        $kw = intval(date('W',strtotime($redaktionsschluss)));
        
        $redaktionsschluss_first = $redaktionsschluss.' '.$stunde.':'.$minute;
        $timestamp = time();
        $timestamp = date("Y-m-d H:i", $timestamp);
        
        if($gerade==0 && $ungerade==0){
            // wenn keine checkbox aktiv
        }else{
            
            if($gerade==1 && $ungerade==1){
            
                // wenn gerade und ungerade Wochen
        
                    DB::table('ausgaben')->insert(
                        array('mandant_id' => $this->mandant->id, 'kw' => $kw, 'jahr' => $jahr, 'redschl' => $redaktionsschluss_first, 'erschdat' => $erscheinungsdatum, 'erscheint' => 1, 'export_revision' => 0, 'created_at' => $timestamp, 'updated_at' => $timestamp)
                    );
                    
                    $anzahl--;

                    
                    while($anzahl>0){
                        
                        $ausgabe_last = Ausgabe::where('mandant_id', '=', $this->mandant->id)->orderBy('erschdat','desc')->first();
                        $kw_neu = ($ausgabe_last->kw)+1;
                        
                        $redaktionsschluss_last = $ausgabe_last->redschl;
                        $erscheinungsdatum_last = $ausgabe_last->erschdat;
                        
                        $erscheinungsdatum_neu = $ausgabe_last->erschdat;
                        
                        $redaktionsschluss_neu = strtotime("+7 day", strtotime($ausgabe_last->redschl));
                        $erscheinungsdatum_neu = strtotime("+7 day", strtotime($ausgabe_last->erschdat));
                        
                        $redaktionsschluss_neu = date("Y-m-d H:i", $redaktionsschluss_neu); 
                        $erscheinungsdatum_neu = date("Y-m-d", $erscheinungsdatum_neu);
                        
                        
                        DB::table('ausgaben')->insert(
                        array('mandant_id' => $this->mandant->id, 'kw' => $kw_neu, 'jahr' => $jahr, 'redschl' => $redaktionsschluss_neu, 'erschdat' => $erscheinungsdatum_neu, 'erscheint' => 1, 'export_revision' => 0, 'created_at' => $timestamp, 'updated_at' => $timestamp)
                        );
                        
                        $anzahl--;
                        
                    }
        
                }else{ // ENDE wenn gerade und ungerade Wochen
                    
                                                          
                    if(gerade==1){
                        // wenn nur gerade aktiv
   
                        
                    }
                    if(ungerade==1){
                        // wenn nur ungerade aktiv
                        
                    }
                    
                }
        
        } // wenn eine checkbox aktiv
        

        
    }
    

    public function ausgabe_status() {
        $r = array();
        $id = Input::get('id');
        $a = Ausgabe::where('id', '=', $id)->first();
        $a->erscheint = Input::get('active');
        $a->save();
        $r['erscheint'] = Input::get('active');
        return $r;
    }

    public function user_details() {
        if ($this->user == null || $this->user->gruppe_id < 2) {
            return array();
        }
        $user = User::find(Input::get('id'));

        if ($user == null) {
            return array();
        }
    
        $beitraege = DB::table('beitraege')->where('user_id', '=', Input::get('id'))->get();
        $anzahlBeitraege = count($beitraege);
        
        
        $text = View::make('modules/user_details')
                ->with('user', $user)
                ->with('admin', $this->user)
                ->with('anzahlBeitraege', $anzahlBeitraege)
                ->render();

        return Response::json(array(
                    'text' => $text
        ));
    }

    public function benutzer_rubriken($u) {
        if ($this->user == null || $this->user->gruppe_id != 2) {
            return Redirect::to('uebersicht');
        }
        $user2 = User::find($u);
        $kats = Kategorie::where('mandant_id', '=', $this->mandant->id)->where('reihenfolge', '>', 1)->orderBy('reihenfolge')->get();
        $k2 = $user2->kategorien()->get();
        $userkats = array();
        foreach ($k2 as $k) {
            $userkats[$k->id] = 1;
        }
        return View::make('rathaus/benutzer_rubriken')
                        ->with('user2', $user2)
                        ->with('kats', $kats)
                        ->with('userkats', $userkats);
    }

    public function benutzerkategorien_speichern() {
        if ($this->user == null || $this->user->gruppe_id != 2) {
            return array();
        }
        $uid = Input::get('uid');
        $ids = Input::get('ids');
        UserKategorien::where('user_id', '=', $uid)->delete();
        foreach ($ids as $id) {
            UserKategorien::create(array(
                'user_id' => $uid,
                'kategorie_id' => $id,
                'aktiv' => 1
            ));
        }
        return array();
    }

    public function ausgabe_details() {
        if ($this->user == null || $this->user->gruppe_id < 2) {
            return array();
        }
        $a = Ausgabe::where('id', '=', Input::get('id'))->first();
        $ed_ = new DateTime($a->erschdat);
        $ed = $ed_->format('d.m.Y');

        $rs_ = new DateTime($a->redschl);
        $rs = $rs_->format('d.m.Y h:i:s');
        return array('ed' => $ed, 'rs' => $rs);
    }
    
    public function ausgabeAktiv() {
            
        $thisid=Input::get('id');
        $erscheint=Input::get('erscheint'); 
        DB::table('ausgaben')
            ->where('id', $thisid)
            ->update(array('erscheint' => $erscheint));
    }
    
    
    
    public function ausgaben_save_detail(){
        $kats = Kategorie::where('mandant_id', '=', $this->mandant->id)->where('reihenfolge', '>', 1)->orderBy('reihenfolge')->get();
        $erscheintam = $_POST["erscheintam"];
        $redaktionsschluss = $_POST["redaktionsschluss"];
        $uhrzeit = $_POST["uhrzeit"];
        $id = $_POST["id"];
        list ($day, $mon, $year) = explode('.', $erscheintam);
        $date = $year.'-'.$mon.'-'.$day.' 12:00';
        $date2 = strtotime($date);
        list ($day2, $mon2, $year2) = explode('.', $redaktionsschluss);
        $date_r = $year2.'-'.$mon2.'-'.$day2.' '.$uhrzeit;
        
        $kw = 0;
        $kw = date('W', $date2);
        
        DB::statement("UPDATE ausgaben SET kw= '$kw' ,jahr = '$year',redschl = '$date_r',erschdat = '$date' WHERE id =$id LIMIT 1");
        
    }

    public function manuskript($kw, $jahr) {
        if ($this->user == null || $this->user->gruppe_id < 2) {
            return Redirect::to('uebersicht');
        }
        $ausgabe = Ausgabe::where('kw', '=', $kw)->where('jahr', '=', $jahr)->where('mandant_id', '=', $this->mandant->id)->first();
        $ausgabe_id = $ausgabe->id;

        $beitraege = DB::table('ausgaben_beitraege')
                ->join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id')
                ->join('kategorien', 'kategorien.id', '=', 'beitraege.kategorie_id')
                ->where('ausgaben_beitraege.ausgabe_id', '=', $ausgabe_id)
                ->whereIn('beitraege.status_id', array(2, 3, 5, 6, 7))
                ->orderBy('kategorien.reihenfolge')
				->orderBy('ausgaben_beitraege.reihenfolge','DESC')
				->orderBy('ausgaben_beitraege.beitrag_id')
				->orderBy('beitraege.id')
                ->select(array('beitraege.id as b', 'kategorien.id as k', 'ausgaben_beitraege.id as abid'))
                ->get();
		// Patch

		
		$katid = -1;
		$r = 1;
		foreach($beitraege as $b) {
			if($b->k != $katid) {
				$katid = $b->k;
				$r = 1;
			} else {
				$r++;
			}
			DB::table('ausgaben_beitraege')
			->where('id',$b->abid)
			->update(['reihenfolge'=>$r]);
		}
		
        $k = DB::table('kategorien')
                        ->leftJoin('eps', 'eps.id', '=', 'kategorien.eps_id')
                        ->where('kategorien.mandant_id', '=', $this->mandant->id)
                        ->orderBy('kategorien.reihenfolge')
                        ->select(array('kategorien.id', 'kategorien.bezeichnung', 'kategorien.hauptkategorie','kategorien.no_headline','eps.filename', 'kategorien.tiefe'))->get();

        $kats = array();
        foreach ($k as $kat) {
            $kats[$kat->id] = $kat;
        }

        $title = 'Manuskript - ' . $this->mandant->bezeichnung . " - Ausgabe $kw/$jahr";

        return View::make('rathaus/manuskript')->with('beitraege', $beitraege)->with('kategorien', $kats)->with('title', $title);
    }

    public function archiv() {
        if ($this->user == null || $this->user->gruppe_id < 2) {
            return Redirect::to('uebersicht');
        }
        
        
        $a = $this->mandant->ausgaben()
                ->join('ausgaben_beitraege', 'ausgaben_beitraege.ausgabe_id', '=', 'ausgaben.id')
                ->join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id')
                ->where('beitraege.status_id', '>=', 5)
                ->select(DB::raw('ausgaben.kw, ausgaben.jahr, ausgaben.id, ausgaben.erschdat,count(`beitraege`.`id`) as c'))
                ->groupBy('ausgaben.kw', 'ausgaben.jahr', 'ausgaben.id', 'ausgaben.erschdat')
                ->distinct()
                ->orderBy('ausgaben.jahr', 'desc')
                ->orderBy('ausgaben.kw', 'desc')
                ->get();
        return View::make('rathaus/archiv')->with('ausgaben', $a);
    }

    public function direktFreigabe() {
        if ($this->user == null || $this->user->gruppe_id < 2) {
            return array();
        }
        $user = User::where('id', Input::get('id'))->first();
        if ($user) {
            $user->direktfreigabe = Input::get('v');
            $user->save();
        }
        return array();
    }

    public function gruppeAendern() {
        $id = Input::get('id');
        if ($this->user == null || $this->user->gruppe_id < 2 || $this->user->id == $id) {
            return array();
        }

        $user = User::where('id', $id)->first();
        if ($user) {
            $user->gruppe_id = Input::get('v');
            $user->save();
        }
        return array('gruppe' => $user->Gruppe()->first()->bezeichnung);
    }
    

    public function changeUser($userid) {

        $fromuser = Session::get('user_id');

        Session::put('user_id', $userid);
        
        if(Session::has('from_user')){
            Session::forget('from_user');
        }else{
            Session::put('from_user', $fromuser);    
        }
        
        return Redirect::to('uebersicht');
        
    }
    
    
    public function deleteUser($userid) {


        DB::table('users')->where('id', '=', $userid)->delete();
        return Redirect::to('rathaus/benutzer');
        
    }
    
    
    public function archiv_details($kw, $jahr) {
        $mandant_id = Session::get('mandant_id');    
        $a = Ausgabe::where('kw', '=', $kw)->where('jahr', '=', $jahr)->where('mandant_id', '=', $mandant_id)->first();

        
        $b = Beitrag::join('ausgaben_beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id')
                        ->where('ausgaben_beitraege.ausgabe_id', '=', $a->id)
                        ->where('beitraege.mandant_id', '=', $mandant_id)
                        ->where('beitraege.status_id', '>=', 5)->get();
                        
//         
        // foreach($b as $ids){
            // $thisid =  $ids["beitrag_id"];
            // DB::table('beitraege')
            // ->where('id', $thisid)
            // ->update(array('status_id' => 7));
        // }
        
        return View::make('rathaus/archiv_details')->with('beitraege', $b)->with('ausgabe', $a);
        
    }

    public function einladungsProtokoll() {
        $einladungen = Einladung::where('mandant_id','=',$this->mandant->id)->orderBy('created_at')->get();
        return View::make('rathaus/einladungsprotokoll')->with('einladungen', $einladungen);

    }
	
	public function beitragVerschieben() {
		$bid = Input::get('bid');
		$aid = Input::get('aid');
		$dir = input::get('dir');
		$b = Beitrag::find($bid);
		
		$kid = $b->kategorie_id;
		
		$rows = DB::table('ausgaben_beitraege')
			->join('beitraege','beitraege.id','=','ausgaben_beitraege.beitrag_id')
			->where('ausgabe_id','=',$aid)
			->where('beitraege.kategorie_id','=',$kid)
			->orderBy('ausgaben_beitraege.beitrag_id')
			->orderBy('ausgaben_beitraege.reihenfolge','DESC')
			->select('ausgaben_beitraege.*')
			->get();
		
		$c = 1000;
		$n = 0;
		$i=0;
		
		foreach($rows as $row) {
			$row->r = $c--;
			if($row->beitrag_id == $bid) {
				$n = $i;
			}
			$i++;
		}
		
		if($dir==1) {
			if($n>0) {
				$rows[$n]->r++;
				$rows[$n-1]->r--;
			}
		} else {
			if($n<count($rows)) {
				$rows[$n]->r--;
				$rows[$n+1]->r++;
			}			
		}

		foreach($rows as $row) {
			if($row->r != $row->reihenfolge) {
				DB::table('ausgaben_beitraege')
					->where('id','=',$row->id)
					->update(['reihenfolge'=>$row->r]);
			}
		}
		
		return [];
		
	}
}
