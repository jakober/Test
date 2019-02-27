<?php

class AjaxController extends BaseController {

    /**
     * Bild löschen
     * @return Response
     */
    public function bild_loeschen() {
        $bild = Bild::find(Input::get('id'));
        if (is_object($bild)) {
            @unlink($bild->getOrigPath());
            @unlink($bild->getMediumPath());
            @unlink($bild->getThumbPath());
            $bild->delete();
        }
        return Response::json(array('id' => Input::get('id')));
    }

    /**
     * Bild löschen
     * @return Response
     */
    public function anhang_loeschen() {
        $anhang = Anhang::find(Input::get('id'));
        if (is_object($anhang)) {
            @unlink($anhang->getPath());
            $anhang->delete();
        }
        return Response::json(array('id' => Input::get('id')));
    }
	
	public function generateIframe(){
		$id = Input::get('id');

		$time = time();
		$date = date('Y-m-d H:i:s',$time);

		DB::table('iframe')
            ->where('id', $id)
            ->update(array('gespeichert' => 1));
		
		$frame = DB::table('iframe')->where('id', $id)->first();

		echo '<iframe src="http://dischingen.amtsblatt-online.biz/user/iframeAusgabe/'.$frame->id.'/'.$frame->iframekey.'" frameborder=0 style="width: '.$frame->width.';height: '.$frame->height.';"><p>Ihr Browser kann leider keine eingebetteten Frames anzeigen </p></iframe>';
		
	}
	
	
	public function generateiframeVorschau(){
		$user = $this->user;
		$userid = $user->id;
		// alte Daten entfernen
		DB::table('iframe')->where('user_id', '=', $userid)->where('gespeichert', '!=', 1)->delete();
		$kats = Input::get('kats');
		$kats_s = serialize($kats);
		$teaser = Input::get('teaser');
		$anzahl = Input::get('anzahl');
		$breite = Input::get('breite');
		$hoehe = Input::get('hoehe');
		$linkcolor = Input::get('linkcolor');
		$bgcolor = Input::get('bgcolor');
		$padding = Input::get('padding');
		$fontcolor = Input::get('fontcolor');
		$time = time();
		$date = date('Y-m-d H:i:s',$time);
		
		$iframekey = md5(rand());
		
		$id = DB::table('iframe')->insertGetId(
		    array('kats' => $kats_s, 'user_id' => $userid, 'iframekey' => $iframekey, 'width' => $breite, 'height' => $hoehe, 'teaser_length' => $teaser, 'anzahl' => $anzahl,'linkcolor' => $linkcolor,'bgcolor' => $bgcolor,'padding' => $padding,'fontcolor' => $fontcolor,'created_at' => $date, 'updated_at' => $date)
		);
		$ausgabe_arr = [];
		$ausgabe_arr['id'] = $id; 
		$ausgabe_arr['iframe'] = '<iframe src="http://dischingen.amtsblatt-online.biz/user/iframeAusgabe/'.$id.'/'.$iframekey.'" frameborder=0 style="width: 100%;height: 100%;">
		 <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen 
		  </p>
		</iframe>';
		echo json_encode($ausgabe_arr);
	}
	

    /**
     * Bild-Text ändern
     * @return Response
     */
    public function st() {
        $bild = Bild::find(Input::get('id'));
        if (is_object($bild)) {
            $bild->bildunterschrift = Input::get('text');
            $bild->save();
            return Response::json(array(
                        'r' => 1
            ));
        }
        return Response::json(array(
                    'r' => 0
        ));
    }

    /**
     * Artikel löschen
     * @return Response
     */
    public function deleteArticle() {
        $beitrag = Beitrag::find(Input::get('id'));
        $bilder = $beitrag->bilder()->get();
        foreach ($bilder as $bild) {
            $bild->delete();
        }
        $beitrag->delete();
        //Session::put('_flash_', 'Der Beitrag "' . $beitrag->ueberschrift . '" wurde gelöscht.');
        return Response::json(array());
    }
        
    public function updateRubriken() {
          $value = $_POST["value"];
          
          
          $id = $_POST["id"];
          $name = $_POST["name"];
          
          if($name=="no_headline" || $name=="export_always"){
              if($value == "true"){
                  $value = 1;
              }else{
                  $value=0;
              }
          }
          if($name == "eps_id"){
              if($value == 0){
                  $value = 'test';
              }else{
                  
              }
          }
          
          DB::statement("UPDATE kategorien SET $name= '$value' WHERE id =$id LIMIT 1");
          
    }
    

    /**
     * Artikel speichern
     * @return Response
     */
    public function saveArticle($rathaus = false) {
        $save = true;
        $r = array(
            'messages' => array()
        );

        $beitrag = Beitrag::find(Input::get('beitrag_id'));
        $wasNew = $beitrag->status_id < 2;

        $beitrag->kategorie_id = Input::get('kategorie_id');
        if ($beitrag->kategorie_id == null) {
            $r['messages'][] = 'Es wurde keine Rubrik ausgewählt';
            $save = false;
        }

        // TODO Checken ob geändert werden darf
        $status = 0;
        if (Input::has('status')) {
            $beitrag->status_id = $status = Input::get('status');
        }

        if (count(Input::get('ausgaben')) == 0 && $beitrag->status_id != 1) {
            $r['messages'][] = 'Es wurde kein Erscheinungstermin ausgewählt';
            $save = false;
        }
        $beitrag->ueberschrift = Input::get('ueberschrift');
        if ($beitrag->ueberschrift == '') {
            $r['messages'][] = 'Es wurde keine Überschrift angegeben';
            $save = false;
        }
        $beitrag->untertitel = Input::get('untertitel');
        $beitrag->text = trim(Input::get('content'));

        if ($beitrag->text == '') {
            $r['messages'][] = 'Der Beitrag enthält keinen Text';
            $save = false;
        }

        //if (!$rathaus) {
        $beitrag->kommentar_rathaus = Input::get('k1') == 1;
        $beitrag->kommentar_redaktion = Input::get('k2') == 1;
        $beitrag->kommentar = trim(Input::get('kommentar'));

        if ($beitrag->kommentar == '') {
            $beitrag->kommentar_rathaus = $beitrag->kommentar_redaktion = false;
        }
        //}

        if ($save && !$rathaus) {

            $tv = Input::get('tv');
            if ($tv) {
                $tv_name = trim(Input::get('tv_name'));
                if ($tv_name != '') {
                    Textvorlage::create(array(
                        'user_id' => $beitrag->user_id,
                        'name' => $tv_name,
                        'kategorie_id' => $beitrag->kategorie_id,
                        'ueberschrift' => $beitrag->ueberschrift,
                        'untertitel' => $beitrag->untertitel,
                        'text' => $beitrag->text
                    ));
                } else {
                    $save = false;
                    $r['messages'][] = 'Es wurde keine Bezeichnung für die Textvorlage angegeben';
                }
            }
        }

        if ($save) {
            $beitrag->save();
            $bid = $beitrag->id;
            AusgabenBeitraege::where('beitrag_id', '=', $bid)->delete();
            $ausgaben = Input::get('ausgaben');
            if (is_array($ausgaben)) {
                foreach ($ausgaben as $id) {
                    AusgabenBeitraege::create(array(
                        'ausgabe_id' => $id,
                        'beitrag_id' => $bid,
                        'exportiert' => false
                    ));
                }
            }
            if ($rathaus) {
                $user = $beitrag->user()->first();
                $mandant = Mandant::find(Session::get('mandant_id'));
                if ($status == 5) {
                    Mail::send('emails/artikel_geaendert_und_freigegeben', array('user' => $user, 'mandant' => $mandant, 'beitrag' => $beitrag), function($message) use($user, $mandant, $beitrag) {
                        $message->from($mandant->email_verwaltung, $mandant->name_verwaltung);
                        $message->to($user->email, $user->name)->subject('Freigabe des Artikels "' . $beitrag->ueberschrift . '" mit Änderungen');
                    });
                }

                Beitragsprotokoll::create(array(
                    'beitrag_id' => $bid,
                    'recipient_user_id' => $beitrag->user_id,
                    'action_user_id' => $this->user->id,
                    'action_id' => (Input::has('status') ? 4 : 2), // geändert und freigegeben
                    'fuer_rathaus' => true,
                    'fuer_redaktion' => true
                ));
            } else {
                if ($beitrag->status_id == 2) {
                    if ($wasNew) {
                        Beitragsprotokoll::create(array(
                            'beitrag_id' => $bid,
                            'recipient_user_id' => $this->user->id,
                            'action_user_id' => $this->user->id,
                            'action_id' => 1, // neu
                            'fuer_rathaus' => true,
                            'fuer_redaktion' => false
                        ));
                    } else {
                        Beitragsprotokoll::create(array(
                            'beitrag_id' => $bid,
                            'recipient_user_id' => $this->user->id,
                            'action_user_id' => $this->user->id,
                            'action_id' => 2, // geändert
                            'fuer_rathaus' => true,
                            'fuer_redaktion' => false
                        ));
                    }
                }
            }
        }

        $r['saved'] = $save;
        if ($save) {
            Session::put('_flash_', 'Der Beitrag "' . $beitrag->ueberschrift . '" wurde gespeichert.');
            unset($r['messages']);
        }

        return Response::json($r);
    }

    public function saveArticle_rathaus() {
        return $this->saveArticle(true);
    }

    public function delTV() {
        //$tv = Textvorlage::find(Input::get('id'));
        $tv = Textvorlage::where('id', '=', Input::get('id'));
        $tv->delete();
        return Response::json(array());
    }

    public function ausgaben() {
        $last_id = Input::get('last_id');
        $ausgabe = Ausgabe::where('id', '=', $last_id)->first();

        $ausgaben = Ausgabe::where('mandant_id', '=', $this->mandant->id)
                        ->where('erschdat', '>', $ausgabe->erschdat)
                        ->where('erscheint', '=', 1)
                        ->orderBy('erschdat')
                        ->take(15)->get();

        $r = array();
        foreach ($ausgaben as $a) {
            $dt = new DateTime($a->erschdat);
            $r[] = array($a->id, $a['kw'], $a['jahr'], $dt->format('d.m.y'));
        }
        return $r;
    }

}
