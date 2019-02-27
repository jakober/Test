<?php

/**
 * Description of UploadController
 *
 * @author schmid
 */
class UserController extends BaseController {

    public function login() {
        $login = Input::get('user');
        $password = Input::get('pw');

        if (strpos($login, '@') === false) {
            $user = User::where('mandant_id', '=', $this->mandant->id)->where('username', '=', $login)->first();
        } else {
        	$user = User::where('mandant_id', '=', $this->mandant->id)->where('email', '=', $login)->first();
        }
        if ($user != null && Hash::check($password, $user->password)) {
            if (!$user->aktiviert) {
                return View::make('user/nicht_aktiviert');
            }
            if ($user->freigeschaltet != 1) {
                return View::make('user/nicht_aktiviert');
            }
            Session::put('user_id', $user->id);
            Session::put('mandant_id', $user->mandant_id);

            if ($user->gruppe_id == 2) {
                return Redirect::to('/rathaus/uebersicht');
            }

            return Redirect::to('uebersicht');
        } else {
            return Redirect::to('/')->with('login_errors', true);
        }
    }

    /*
      if (Auth::attempt($userdata)) {
      $user = Auth::user();
      if (!$user->aktiviert) {
      return View::make('user/nicht_aktiviert');
      }
      if ($user->freigeschaltet!=1) {
      return View::make('user/nicht_aktiviert');
      }
      Session::put('user_id', $user->id);
      Session::put('mandant_id', $user->mandant_id);

      if($user->gruppe_id==2) {
      return Redirect::to('/rathaus/uebersicht');
      }

      return Redirect::to('uebersicht');
      } else {
      return Redirect::to('/')->with('login_errors', true);
      }
     *
     */

    public function logout() {
        Auth::logout();
        Session::flush();
        return Redirect::to('auf_wiedersehen');
    }

    public function registrieren() {
        return View::make('user/bearbeiten');
    }

    public function bearbeiten() {
        if ($this->user == null) {
            return Redirect::to('/');
        }

        return View::make('user/meine_daten')
                        ->with('messages', array());
    }

    public function bearbeiten_speichern() {
        $msgs = array();
        $user = $this->user;

        $email = trim(Input::get('email'));
        if ($user->email == '') {
            $msgs[] = 'Keine E-Mail angegeben';
        } else if ($email != $user->email) {
            $count = User::where('email', '=', $user->email)->where('mandant_id', '=', $this->mandant->id)->count();
            if ($count > 0) {
                $msgs[] = 'Die E-Mail-Adresse wird bereits verwendet';
            } else {
                $user->email = $email;
                $validator = Validator::make(array('email' => $user->email), array('email' => 'email'));
                if ($validator->fails()) {
                    $msgs[] = 'Ungültige E-Mail-Adresse';
                }
            }
        }
        $user->anrede = Input::get('anrede');
        if ($user->anrede == '') {
            $msgs[] = 'Keine Anrede angegeben';
        }

        $user->vorname = trim(Input::get('vorname'));
        if ($user->vorname == '') {
            $msgs[] = 'Kein Vorname angegeben';
        }

        $user->name = trim(Input::get('name'));
        if ($user->name == '') {
            $msgs[] = 'Kein Name angegeben';
        }

        $user->firma = trim(Input::get('firma'));
        if ($user->anrede == 'f' && $user->firma == '') {
            $msgs[] = 'Keine Firma angegeben';
        }

        $user->strasse = trim(Input::get('strasse'));
        if ($user->strasse == '') {
            $msgs[] = 'Keine Straße angegeben';
        }

        $user->plz = trim(Input::get('plz'));
        if ($user->plz == '') {
            $msgs[] = 'Keine PLZ angegeben';
        }

        $user->ort = trim(Input::get('ort'));
        if ($user->ort == '') {
            $msgs[] = 'Kein Ort angegeben';
        }

        $user->telefon = trim(Input::get('telefon'));
        if ($user->telefon == '') {
            $msgs[] = 'Kein Telefon angegeben';
        }

        $user->mobilnummer = trim(Input::get('mobil'));
        $user->fax = trim(Input::get('fax'));

        $kw_alt = Input::get('altes_kennwort');
        $kw = Input::get('kennwort');
        $kw2 = Input::get('kennwort2');

        if (Hash::check($kw_alt, $user->password)) {
            if ($kw . $kw2 != '') {
                if ($kw != $kw2) {
                    $msgs[] = 'Das neue Passwort stimmen nicht mit der Wiederholung überein';
                } else if (strlen($kw) < 6) {
                    $msgs[] = 'Das neue Passwort ist zu kurz';
                } else {
                    if (count($msgs) == 0) {
                        $user->password = Hash::make($kw);
                    }
                }
            }
        } else {
            $msgs[] = 'Das alte Passwort ist falsch!';
        }

        if (count($msgs) > 0) {
            return View::make('user/meine_daten')->with('messages', $msgs);
        } else {
            $user->save();
            Session::put('_flash_', 'Ihre Daten wurden geändert.');
            return Redirect::to('/uebersicht');
        }
    }

    public function register() {
        if (Session::has('user')) {
            return Redirect::to('/uebersicht');
        }

        $user = new User();
        Session::put('newuser', $user);

        if (!Session::has('captcha_proofed')) {
            $x = rand(1, 9);
            $y = rand(1, 9);
            Session::put('summe', $x + $y);
            Session::put('x', $x);
            Session::put('y', $y);
        }
        return View::make('user/register')
                        ->with('newuser', $user)
                        ->with('messages', array());
    }

    public function register_post() {
        $user = Session::get('newuser');
        $user->mandant_id = $this->mandant->id;

        $msgs = array();

//        $username = trim(Input::get('username'));
//        $count = User::where('username', '=', $username)->where('mandant_id', '=', $this->mandant->id)->count();

//        if ($username == '') {
//            $msgs[] = 'Kein Benutzername angegeben';
//        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $username)) {
//            $msgs[] = 'Der Benutzername enthält ungültige Zeichen. Es sind nur Ziffern und Buchstaben erlaubt.';
//        }
//        if ($count > 0) {
//            $msgs[] = 'Der Benutzername existiert bereits';
//        }
//        $user->username = $username;
        $user->username = '';

        $user->email = trim(Input::get('email'));
        if ($user->email == '') {
            $msgs[] = 'Keine E-Mail angegeben';
        } else {
            $count = User::where('email', '=', $user->email)->where('mandant_id', '=', $this->mandant->id)->count();
            $validator = Validator::make(array('email' => $user->email), array('email' => 'email'));
            if ($validator->fails()) {
                $msgs[] = 'Ungültige E-Mail-Adresse';
            }  else if ($count > 0) {
                $msgs[] = 'Die E-Mail-Adresse wird bereits verwendet';
            }
        }

        if ($user->password == '') {
            $kw = Input::get('kennwort');
            if ($kw == '') {
                $msgs[] = 'Kein Passwort ausgewählt';
            } else if ($kw != Input::get('kennwort2')) {
                $msgs[] = 'Die Passwörter stimmen nicht überein';
            } else if (strlen($kw) < 6) {
                $msgs[] = 'Das Passwort ist zu kurz';
            } else {
                $user->password = Hash::make($kw);
            }
        }

        $user->anrede = Input::get('anrede');
        if ($user->anrede == '') {
            $msgs[] = 'Keine Anrede angegeben';
        }

        $user->vorname = trim(Input::get('vorname'));
        if ($user->vorname == '') {
            $msgs[] = 'Kein Vorname angegeben';
        }

        $user->name = trim(Input::get('name'));
        if ($user->name == '') {
            $msgs[] = 'Kein Name angegeben';
        }

        $user->firma = trim(Input::get('firma'));
        if ($user->anrede == 'f' && $user->firma == '') {
            $msgs[] = 'Keine Firma angegeben';
        }

        $user->strasse = trim(Input::get('strasse'));
        if ($user->strasse == '') {
            $msgs[] = 'Keine Straße angegeben';
        }

        $user->plz = trim(Input::get('plz'));
        if ($user->plz == '') {
            $msgs[] = 'Keine PLZ angegeben';
        }

        $user->ort = trim(Input::get('ort'));
        if ($user->ort == '') {
            $msgs[] = 'Kein Ort angegeben';
        }

        $user->telefon = trim(Input::get('telefon'));
        if ($user->telefon == '') {
            $msgs[] = 'Kein Telefon angegeben';
        }

        $user->mobilnummer = trim(Input::get('mobil'));
        $user->fax = trim(Input::get('fax'));

        if (Session::has('summe')) {
            $summe = intval(Input::get('summe'));
            if ($summe != session::get('summe')) {
                $msgs[] = 'Sicherheitsfrage wurde nicht korrekt beantwortet';
                $x = rand(1, 9);
                $y = rand(1, 9);
                Session::put('summe', $x + $y);
                Session::put('x', $x);
                Session::put('y', $y);
            } else {
                Session::put('captcha_proofed', 1);
                Session::forget('summe');
                Session::forget('x');
                Session::forget('y');
            }
        }

        Session::put('newuser', $user);
        if (count($msgs) > 0) {
            return View::make('user/register')->with('newuser', $user)->with('messages', $msgs);
        } else {
            return Redirect::To('user/meine_kategorien');
        }
    }

    public function registrierungsInfo() {
        $user = Session::get('newuser');
        Session::forget('newuser');
        return View::make('user/registrierungsinfo')
                        ->with('newuser', $user);
    }

    public function registrierung_abschliessen() {
        $user = User::where('id', '=', Input::get('id'))->where('mandant_id', '=', $this->mandant->id)->first();
        if ($user == null) {
            return View::make('user/nicht_gefunden');
        } else {
            if ($user->aktiviert) {
                return View::make('user/bereits_aktiv');
            }
        }
        $key = Aktivierungsschluessel::where('id', '=', $user->id)->first();
        if (Input::get('key') != $key->key) {
            return View::make('user/falscher_key');
        }
        $user->aktiviert = true;
        $user->save();
        $key->delete();
        return View::make('user/aktiviert');
    }

    public function artikelVerwaltung() {
        if ($this->user == null) {
            return Redirect::to('/');
        }
        $ausgaben = Ausgabe::next($this->user->mandant_id, 999999)->get();
        return View::make('user/artikelverwaltung')
                        ->with('ausgaben', $ausgaben)
                        ->with('user', $this->user);
    }

    public function kennwort_vergessen() {

        if (!Input::has('identification')) {
            return View::make('user/kennwort_vergessen');
        }
        $id = Input::get('identification');

        $user = User::where('email', '=', $id)->where('mandant_id', '=', $this->mandant->id)->first();
        $mandant = $this->mandant;
        if ($user) {

            $link = 'http://' . $mandant->hostname . '/kennwort_aendern?u=' . $user->id . '&amp;token=' . md5($user->password);
            Mail::send('emails/neues_kennwort', array('user' => $user, 'mandant' => $mandant, 'link' => $link), function($message) use($user, $mandant) {
                $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
                $message->to($user->email, $user->name)->subject('Anforderung eines neuen Passworts');
            });
            return View::make('user/kennwort_vergessen_email_nachricht')->with('pwuser', $user);
        }

        return View::make('user/kennwort_vergessen_email_unbekannt');
    }

    public function kennwort_vergessen_get() {
        if (!Input::has('u') || !Input::has('token')) {
            return Redirect::to('/uebersicht');
        }

        $user = User::where('id', '=', Input::get('u'))->first();
        if ($user) {
            if (md5($user->password) == Input::get('token')) {
                Session::put('pwuser', $user);
                return View::make('user/neues_kennwort_form');
            }
        }
        return View::make('user/nicht_gefunden_oder_kennwort_geaendert');
    }

    public function kennwort_vergessen_post() {
        $pw = Input::get('password');
        $pw2 = Input::get('password2');

        if ($pw == $pw2) {
            if (strlen($pw) < 6) {
                return View::make('user/neues_kennwort_form')->with('message', 'Das Passwort ist zu kurz!');
            }
        } else {
            return View::make('user/neues_kennwort_form')->with('message', 'Die Passwörter stimmen nicht überein!');
        }

        $user = Session::get('pwuser');
        $user->password = Hash::make($pw);
        $user->save();
        Session::forget('pwuser');
        return View::make('user/neues_kennwort_geaendert');
    }

}
