<?php

class ArtikelController extends BaseController {
    /*
      public function neu() {
      if ($this->user == null) {
      return Redirect::to('/');
      }
      $beitrag = Beitrag::where('user_id', '=', $this->user->id)->where('status_id', '=', '0')->first();

      if ($beitrag == null) {
      $beitrag = Beitrag::create(
      array(
      'mandant_id' => $this->user->mandant_id,
      'kategorie_id' => null,
      'user_id' => $this->user->id,
      'status_id' => 0
      )
      );
      }

      return $this->createEditView($beitrag, 1);
      }
     */

    public function neu() {
        if ($this->user == null) {
            return Redirect::to('/');
        }
        $beitrag_alt = Beitrag::where('user_id', '=', $this->user->id)->where('status_id', '=', '0')->first();

        if ($beitrag_alt != null) {
            $beitrag_alt->delete();
        }

        $beitrag = Beitrag::create(
                        array(
                            'mandant_id' => $this->user->mandant_id,
                            'kategorie_id' => null,
                            'user_id' => $this->user->id,
                            'status_id' => 0
                        )
        );

        return $this->createEditView($beitrag, 1);
    }

    public function neuMitVorlage($id) {
        if ($this->user == null) {
            return Redirect::to('/');
        }

        // $vorlage = Textvorlage::find($id)->first(); // funktioniert nicht!?
        $vorlage = Textvorlage::where('id', '=', $id)->first();

        if ($vorlage != null) {
            $beitrag = Beitrag::create(
                            array(
                                'mandant_id' => $this->user->mandant_id,
                                'kategorie_id' => $vorlage->kategorie->id,
                                'user_id' => $this->user->id,
                                'ueberschrift' => $vorlage->ueberschrift,
                                // 'untertitel' => $vorlage->untertitel,
                                'text' => $vorlage->text,
                                'status_id' => 0
                            )
            );
            return $this->createEditView($beitrag, 1);
        } else {
            return Redirect::to('/artikel/textvorlagen');
        }
    }

    public function kopieren($id) {
        if ($this->user == null) {
            return Redirect::to('/');
        }

        // $vorlage = Textvorlage::find($id)->first(); // funktioniert nicht!?
        $vorlage = Beitrag::where('id', '=', $id)->first();
        if ($vorlage->user_id != $this->user->id) {
            return Redirect::to('/');
        }

        if ($vorlage != null) {
            $beitrag = Beitrag::create(
                            array(
                                'mandant_id' => $this->user->mandant_id,
                                'kategorie_id' => $vorlage->kategorie->id,
                                'user_id' => $this->user->id,
                                'ueberschrift' => $vorlage->ueberschrift,
                                'untertitel' => $vorlage->untertitel,
                                'text' => $vorlage->text,
                                'status_id' => 0
                            )
            );
            $copy = function($src, $dst) {
                if (file_exists($src)) {
                    copy($src, $dst);
                }
            };
            foreach ($vorlage->bilder()->get() as $bild) {
                $end = strtolower(File::extension($bild->localfilename));
                do {
                    $localfilename = Str::random(20) . '.' . $end;
                    $dstT = public_path() . '/artikelbilder/thumb/' . $localfilename;
                } while (file_exists($dstT));

                $copy(public_path() . '/artikelbilder/thumb/' . $bild->localfilename, public_path() . '/artikelbilder/thumb/' . $localfilename);
                $copy(public_path() . '/artikelbilder/medium/' . $bild->localfilename, public_path() . '/artikelbilder/medium/' . $localfilename);
                $copy(public_path() . '/artikelbilder/' . $bild->localfilename, public_path() . '/artikelbilder/' . $localfilename);
                $fields = array('filename', 'localfilename', 'mimetype', 'bildunterschrift', 'w', 'h', 'ww', 'wh', 'tw', 'th');
                $o = array();
                foreach ($fields as $f) {
                    $o[$f] = $bild->$f;
                }
                $o['beitrag_id'] = $beitrag->id;
                Bild::create($o);
            }

            foreach ($vorlage->anhaenge()->get() as $anhang) {
                $path = storage_path() . '/anhaenge/' . $beitrag->id;
                if (!file_exists($path)) {
                    mkdir($path);
                }

                $copy(storage_path() . '/anhaenge/' . $vorlage->id . '/' . $anhang->filename, storage_path() . '/anhaenge/' . $beitrag->id . '/' . $anhang->filename);
                $fields = array('filename', 'mimetype', 'size');
                $o = array();
                foreach ($fields as $f) {
                    $o[$f] = $anhang->$f;
                }
                $o['beitrag_id'] = $beitrag->id;
                Anhang::create($o);
            }

            return $this->createEditView($beitrag, 2);
        } else {
            return Redirect::to('/artikel/textvorlagen');
        }
    }

    public function bearbeiten() {
        $id = Input::get('id');
        return $this->edit($id);
    }

    public function edit($id, $modus = 2) {
        if ($this->user == null) {
            return Redirect::to('/');
        }
        $beitrag = Beitrag::find($id);

        if ($beitrag == null) {
            return View::make('artikel/artikel_nicht_gefunden');
        }

        if (!$beitrag->canBeEditedBy($this->user) && $this->user->gruppe_id < 2) {
            return View::make('artikel/keine_berechtigung_e');
        }

        if (!$beitrag->isEditable() && $this->user->gruppe_id == 1) {
            return View::make('artikel/nicht_editierbar')->with('beitrag', $beitrag);
        }
        return $this->createEditView($beitrag, $modus);
    }

    public function edit_rathaus() {
        return $this->edit(Input::get('id'), 3);
    }


    public function artikelvorschau($id) {
        if ($this->user == null) {
            return Redirect::to('/');
        }

 
        
        $beitrag = Beitrag::find($id);

        if ($beitrag == null) {
            return View::make('artikel/artikel_nicht_gefunden');
        }

        if (!$beitrag->canBeEditedBy($this->user) && $this->user->gruppe_id < 2) {
            return View::make('artikel/keine_berechtigung_e');
        }

        if (!$beitrag->isEditable() && $this->user->gruppe_id == 1) {
            return View::make('artikel/nicht_editierbar')->with('beitrag', $beitrag);
        }

        $mandant = $this->user->mandant()->first();
        if ($this->user->gruppe_id > 1) {
            $kategorien = Kategorie::where('mandant_id', '=', $this->user->mandant_id)->where('reihenfolge', '>', 1)->orderBy('reihenfolge')->get();
            $ausgaben = $mandant->ausgaben()->nextB($mandant->id, 6)->get();
                } else {
            $kategorien = $this->user->kategorien()->where('aktiv', '=', '1')->orderBy('reihenfolge')->get();
            $ausgaben = $mandant->ausgaben()->next($mandant->id, 6)->get();
        }
        
        $alteAusgaben = $beitrag->ausgaben()->where('redschl', '<', new DateTime())->take(1)->get();

        //$ausgaben = $mandant->ausgaben()->where('redschl', '>', new DateTime())->orderBy('erschdat')->take(6)->get();
        $bilder = $beitrag->bilder()->get();
        $anhaenge = $beitrag->anhaenge()->get();

        $artikelAusgaben = $beitrag->ausgaben()->orderBy('ausgaben.erschdat')->get();

        $ids = array();
        if (count($artikelAusgaben) > 0 && count($ausgaben) > 0) {
            foreach ($artikelAusgaben as $a) {
                $ids[$a->id] = 1;
            }

            $last1 = $ausgaben[count($ausgaben) - 1];
            $last2 = $artikelAusgaben[count($artikelAusgaben) - 1];

            if ($last1->erschdat < $last2->erschdat) {
                $ausgaben = Ausgabe::where('mandant_id', '=', $this->mandant->id)
                                ->where('redschl', '>=', new DateTime())
                                ->where('erscheint', '=', 1)
                                ->where('erschdat', '<=', $last2->erschdat)
                                ->orderBy('erschdat')->get();
            }
        }
        
        
        
         return View::make('artikel/artikelvorschau')->with('beitrag',$beitrag)->with('bilder',$bilder)->with('anhaenge',$anhaenge)->with('kategorien',$kategorien)
         ->with('modus', '2')
         ->with('artikelAusgaben', $artikelAusgaben)
         ;
                        
    }

    /**
     *
     * @param type $beitrag
     * @param type $modus (1=neu,2=bearbeiten,3=Fremdbearbeitung)
     * @return type
     */
    private function createEditView($beitrag, $modus) {
        $mandant = $this->user->mandant()->first();
        if ($this->user->gruppe_id > 1) {
            $kategorien = Kategorie::where('mandant_id', '=', $this->user->mandant_id)->where('reihenfolge', '>', 1)->orderBy('reihenfolge')->get();
            $ausgaben = $mandant->ausgaben()->nextB($mandant->id, 6)->get();
                } else {
            $kategorien = $this->user->kategorien()->where('aktiv', '=', '1')->orderBy('reihenfolge')->get();
            $ausgaben = $mandant->ausgaben()->next($mandant->id, 6)->get();
        }

        if (count($kategorien) == 0) {
            return View::make('artikel/keine_kategorien');
        }

        $alteAusgaben = $beitrag->ausgaben()->where('redschl', '<', new DateTime())->take(1)->get();

        //$ausgaben = $mandant->ausgaben()->where('redschl', '>', new DateTime())->orderBy('erschdat')->take(6)->get();
        $bilder = $beitrag->bilder()->get();
        $anhaenge = $beitrag->anhaenge()->get();

        $artikelAusgaben = $beitrag->ausgaben()->orderBy('ausgaben.erschdat')->get();

        $ids = array();
        if (count($artikelAusgaben) > 0 && count($ausgaben) > 0) {
            foreach ($artikelAusgaben as $a) {
                $ids[$a->id] = 1;
            }

            $last1 = $ausgaben[count($ausgaben) - 1];
            $last2 = $artikelAusgaben[count($artikelAusgaben) - 1];

            if ($last1->erschdat < $last2->erschdat) {
                $ausgaben = Ausgabe::where('mandant_id', '=', $this->mandant->id)
                                ->where('redschl', '>=', new DateTime())
                                ->where('erscheint', '=', 1)
                                ->where('erschdat', '<=', $last2->erschdat)
                                ->orderBy('erschdat')->get();
            }
        }

        return View::make('artikel/bearbeiten')
                        ->with('beitrag', $beitrag)
                        ->with('kategorien', $kategorien)
                        ->with('ausgaben', $ausgaben)
                        ->with('bilder', $bilder)
                        ->with('anhaenge', $anhaenge)
                        ->with('tv', false)
                        ->with('tv_name', '')
                        ->with('modus', $modus)
                        ->with('artikelAusgaben', $ids)
                        ->with('alteAusgaben',$alteAusgaben);
    }

    public function archiv() {
        if ($this->user == null) {
            return Redirect::to('/');
        }

        $beitraege = $this->user->beitraege()->where('status_id', '>=', 5)->orderBy('updated_at', 'desc')->paginate(25);
        
        return View::make('user/archiv')->with('beitraege', $beitraege);

        /*
          $rows = Beitrag::join('ausgaben_beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id')
          ->join('ausgaben', 'ausgaben.id', '=', 'ausgaben_beitraege.ausgabe_id')
          ->join('kategorien', 'kategorien.id', '=', 'beitraege.kategorie_id')
          ->where('ausgaben_beitraege.exportiert','=',1)
          ->where('beitraege.user_id', '=', $this->user->id)
          ->orderBy('ausgaben.jahr','desc')
          ->orderBy('ausgaben.kw','desc')
          ->orderBy('beitrag_id')
          ->get(array('beitrag_id', 'ausgabe_id', 'kategorien.bezeichnung', 'beitraege.ueberschrift'));

          return View::make('user/archiv')->with('rows', $rows)->with('ausgaben', array());
          //return View::make('baustelle')->with('h1','Archiv');
         */
    }

    public function view($id) {
        if ($this->user == null) {
            return Redirect::to('/');
        }

        $beitrag = Beitrag::find($id);

        if ($beitrag == null) {
            return View::make('artikel/artikel_nicht_gefunden_a');
        }

        if ($this->user->id != $beitrag->user_id && $this->user->gruppe_id < 2) {
            return View::make('artikel/keine_berechtigung_a');
        }

        $kat = $beitrag->kategorie()->first();
        $bilder = $beitrag->bilder()->get();
        $anhaenge = $beitrag->anhaenge()->get();

        return View::make('artikel/view')
                        ->with('beitrag', $beitrag)
                        ->with('user', $this->user)
                        ->with('created_at', new DateTime($beitrag->created_at))
                        ->with('updated_at', new DateTime($beitrag->updated_at))
                        ->with('kategorie', $kat)
                        ->with('bilder', $bilder)
                        ->with('anhaenge', $anhaenge)
                        ->with('status', $beitrag->status()->first());
    }

    public function details() {
        $beitrag = Beitrag::find(Input::get('id'));

        if ($beitrag == null) {
            return array('error' => 'Beitrag existiert nicht!');
        }

        if (!$beitrag->canBeEditedBy($this->user) && $this->user->gruppe_id == 1) {
            return array('error' => 'Keine Berechtigung!');
            //return View::make('artikel/keine_berechtigung_a');
        }

        $kat = $beitrag->kategorie()->first();
        $bilder = $beitrag->bilder()->get();
        $anhaenge = $beitrag->anhaenge()->get();

        $text = View::make('modules/artikel_details')
                ->with('beitrag', $beitrag)
                ->with('user', $this->user)
                ->with('created_at', new DateTime($beitrag->created_at))
                ->with('updated_at', new DateTime($beitrag->updated_at))
                ->with('kategorie', $kat)
                ->with('bilder', $bilder)
                ->with('anhaenge', $anhaenge)
                ->with('status', $beitrag->status()->first())
                ->render();

        return Response::json(array(
                    'text' => $text, 'status'=>$beitrag->status_id
        ));
    }

}
