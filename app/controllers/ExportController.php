<?php

class ExportController extends BaseController {

	public function tree() {
		Return View::make('export/tree');
	}

	public function uebersicht() {
	    
		$ausgaben = Ausgabe::nextB($this -> mandant -> id, 6) -> get();
		Return View::make('export/uebersicht') -> with('ausgaben', $ausgaben);
	}
    public static function getBeitraegeNeu($ausgabe, $jahr){
      
      $ausgabe = Ausgabe::where('kw', '=', $ausgabe) -> where('jahr', '=', $jahr) -> where('mandant_id', '=', Session::get('mandant_id')) -> first();
        if ($ausgabe == null) {
            // TODO Ausgabe existiert nicht
            return null;
        }
        $ausgabe_id = $ausgabe -> id;
        

        $beitrag_ids = DB::table('ausgaben_beitraege')
      -> join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id')
      -> join('kategorien', 'kategorien.id', '=', 'beitraege.kategorie_id')
      -> where('ausgaben_beitraege.ausgabe_id', '=', $ausgabe_id)
	  -> where('ausgaben_beitraege.exportiert', '!=', 1)
      -> whereIn('beitraege.status_id', array(5,6))
      -> orderBy('kategorien.reihenfolge')
      -> orderBy('ausgaben_beitraege.reihenfolge')
      -> select(array('beitraege.id'))
      -> get();
      
     
      
      return count($beitrag_ids);
    }
    public static function getBeitraege($ausgabe, $jahr){
        
      $ausgabe = Ausgabe::where('kw', '=', $ausgabe) -> where('jahr', '=', $jahr) -> where('mandant_id', '=', Session::get('mandant_id')) -> first();
        if ($ausgabe == null) {
            // TODO Ausgabe existiert nicht
            return null;
        }
        $ausgabe_id = $ausgabe -> id;
        

        $beitrag_ids = DB::table('ausgaben_beitraege')
      -> join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id')
      -> join('kategorien', 'kategorien.id', '=', 'beitraege.kategorie_id')
      -> where('ausgaben_beitraege.ausgabe_id', '=', $ausgabe_id)
      -> whereIn('beitraege.status_id', array(5,6,7))
      -> orderBy('kategorien.reihenfolge')
      -> orderBy('ausgaben_beitraege.reihenfolge')
      -> select(array('beitraege.id'))
      -> get();
      
      return count($beitrag_ids);
    }
	public function export($ausgabe, $jahr, $method) {
    

		if ($this -> user == null || $this -> user -> gruppe_id < 3) {
			return Redirect::to('uebersicht');
		}

		$ausgabe = Ausgabe::where('kw', '=', $ausgabe) -> where('jahr', '=', $jahr) -> where('mandant_id', '=', $this -> mandant -> id) -> first();
		
		
		
		if ($ausgabe == null) {
			// TODO Ausgabe existiert nicht
			return null;
		}
		$ausgabe_id = $ausgabe -> id;
		Session::put('ausgabe', $ausgabe_id);
        
		DB::table('ausgaben_export') -> insert(array('ausgabe_id' => $ausgabe_id));
        
        
        
        
          if($method=="alle" || $method=="noimage" || $method=="justimages"){
            $statusids = array(5,6,7);
          }else{
            $statusids = array(5,6);
          }


		$eps_all = DB::table('eps') -> get();
		$getBeitragids = DB::table('ausgaben_beitraege');
	      $getBeitragids-> join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id');
	      $getBeitragids-> join('kategorien', 'kategorien.id', '=', 'beitraege.kategorie_id');
	      $getBeitragids-> where('ausgaben_beitraege.ausgabe_id', '=', $ausgabe_id);
		  if($method!="alle" && $method!="noimage" && $method!="justimages"){
		  	$getBeitragids-> where('ausgaben_beitraege.exportiert', '!=', 1);
		  }
	      $getBeitragids-> whereIn('beitraege.status_id', $statusids);
		  $getBeitragids-> where('beitraege.id','!=', '13129');
	      $getBeitragids-> orderBy('kategorien.reihenfolge');
	      $getBeitragids-> orderBy('ausgaben_beitraege.reihenfolge');
	      $getBeitragids-> select(array('beitraege.id'));
	      $beitrag_ids = $getBeitragids->get();
		
		
		
		

    $beitraege = [];
    
    foreach($beitrag_ids as $id) {
      $beitraege[] = Beitrag::where('id', $id->id)->first();
    }
		//$beitraege = Beitrag::whereIn('id', $a) -> get();


		$ausgabe -> export_revision += 1;
		$ausgabe -> save();

		$rev = $ausgabe -> export_revision;
		$mandant = $ausgabe -> mandant() -> first();

		$zip = new ZipArchive();
		$zipfilename = str_replace(' ', '_', strtolower($mandant -> bezeichnung)) . '_ausgabe_' . $ausgabe -> kw . '_' . $ausgabe -> jahr . '_R' . $rev;
		$dir = storage_path() . '/export/' . $zipfilename;
        
        
        
        
		//mkdir($dir);
		//mkdir($dir . '/bilder');
		//mkdir($dir . '/anhaenge');
		
		
		
		$zip -> open($dir . '.zip', ZIPARCHIVE::CREATE);
		$zip -> addEmptyDir($zipfilename);

		if($method!="justimages"){
		
		$zip -> addEmptyDir($zipfilename . '/anhaenge');
		//$zip->addFile(public_path() . '/dtds/exportschema.dtd', $zipfilename . '/exportschema.dtd');
		foreach ($beitraege as $b) {
			$anhaenge = $b -> anhaenge() -> get();
			foreach ($anhaenge as $anhang) {
				if (file_exists($anhang -> getPath())) {
					$zip -> addFile($anhang -> getPath(), $zipfilename . '/anhaenge/' . $anhang -> getExportFileName());
				}
			}
		}
        }
        
        $mandant_query = DB::table('mandanten')->where('id', $this->mandant->id)->first();;
        $spaltenbreite = $mandant_query->spaltenbreite;
        
        if($spaltenbreite==""){
             $spaltenbreite = "164";
        }
        if($method!="noimage"){
    		$zip -> addEmptyDir($zipfilename . '/bilder');
            $zip -> addEmptyDir($zipfilename . '/originalbilder');
        }
    
        //////// WIEDER EINBLENDEN ///////////
    
    
    	foreach ($beitraege as $b) {
			$bilder = $b -> bilder() -> get();
			if (count($bilder)) {
			    if($method!="noimage"){
				$zip -> addEmptyDir($dirname1 = $zipfilename . '/bilder/' . $b -> id);
				$zip -> addEmptyDir($dirname2 = $zipfilename . '/originalbilder/' . $b -> id);
                
				$dirname1 .= '/';
				$dirname2 .= '/';
                }
			}
            if($method!="noimage"){
			foreach ($bilder as $bild) {
				if (file_exists($origPath = $bild -> getOrigPath())) {
					$zip -> addFile($origPath, $dirname2 . $bild -> getExportFileName());
	                
	                if($method!="justimages"){
    	                $resizer = Resizer::open($origPath);
                        $tmpfile = storage_path() . '/tmp/' . Str::random(20) . '.' . strtolower(File::extension($origPath));
        	           
        	           /// Zeile Resizer Ausblenden wenn Probleme /////
        	           
        	           $resizer->resize($spaltenbreite, null, 'landscape')->save($tmpfile);
    					$zip -> addFile($tmpfile, $dirname1 . $bild -> getExportFileName());
                    
                    }else{
                        $zip -> addFile($origPath, $dirname1 . $bild -> getExportFileName());
                    }
                    
				}
			}
            
            }
		}

		
		
        
        
        
        
        
        
		// $zip->addEmptyDir($zipfilename . '/eps');
		// $eps_all = DB::table('eps')->get();
// 		
// 		
		// foreach ($eps_all as $eps) {
		  // $epspfad = public_path().'/eps/' . $this->mandant->id . '/' .$eps->filename.'.eps';
// 
		  // if (file_exists($epspfad)) {
		    // $zip->addFile($epspfad, $zipfilename . '/eps/' . $this->mandant->id . '/' . $eps->filename.'.eps');
		  // }
		// }
        
		$rubriken = DB::table('kategorien') -> where('mandant_id', '=', $this -> mandant -> id) -> select(array('id', 'bezeichnung', 'hauptkategorie', 'tiefe', 'xml_tag', 'eps_id', 'export_always', 'headline_style', 'untertitel_style', 'absatz_style')) -> get();

		$kats = [];
		$kb = [];
        
		foreach ($rubriken as $kat) {
			$id = $kat -> id;
			$kats[$id] = $kat;
			$kb[$id] = [];

			$kat -> show = $kat -> export_always;
		}
		
		foreach ($beitraege as $b) {
			$kats[$b -> kategorie_id] -> show = 1;
			$kb[$b -> kategorie_id][] = $b;
		}

		$iterate = true;
		while ($iterate) {
			$iterate = false;
			foreach ($rubriken as $kat) {
				if ($kat -> show) {
					if ($id = $kat -> hauptkategorie) {
						if ($kats[$id] -> show == 0) {
							$iterate = true;
							$kats[$id] -> show = 1;
						}
					}
				}
			}
		}
        
    // foreach ($beitraege as $o) {
			// $status_neu = 7;
			// DB::table('beitraege')
				// ->where('id', $o->id)
				// ->update(array('status_id' => $status_neu));
    // }
	if($method!="test"){
		foreach ($beitraege as $o) {
				//$status_neu = 7;
				$exportiert = 1;
				DB::table('ausgaben_beitraege')
					->where('beitrag_id', $o->id)
					->where('ausgabe_id',$ausgabe_id)
					->update(array('exportiert' => $exportiert));
	    }
	}
        
        
        
		$content = View::make('export/xml2_m') -> with(['rubriken' => $rubriken, 'beitraege' => $beitraege, 'kb' => $kb, 'mandant'=>$this->mandant->id]);
        
        if($method!="justimages"){
        
		  $zip -> addFromString($zipfilename . '/inhalt.xml', $content);
        
        
        }
        
		$zip -> close();
		

		//return Response::make($view, 200, array('Content-Type' => 'text/xml'));
		
		return Response::download($dir . '.zip');
	}

	public function export_test($ausgabe) {

		//		$ausgabe = 43;
$jahr = date('Y',time());

		if ($this -> user == null || $this -> user -> gruppe_id < 3) {
			return Redirect::to('uebersicht');
		}

		$ausgabe = Ausgabe::where('kw', '=', $ausgabe) -> where('jahr', '=', $jahr) -> where('mandant_id', '=', $this -> mandant -> id) -> first();

		if ($ausgabe == null) {
			// TODO Ausgabe existiert nicht
			return null;
		}

		$ausgabe_id = $ausgabe -> id;
		Session::put('ausgabe', $ausgabe_id);

		/*
		 DB::table('ausgaben_export')->insert(
		 array('ausgabe_id' => $ausgabe_id)
		 );
		 */

		//$eps_all = DB::table('eps')->get();

		$beitraege = DB::table('ausgaben_beitraege') -> join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id') -> join('kategorien', 'kategorien.id', '=', 'beitraege.kategorie_id') -> where('ausgaben_beitraege.ausgabe_id', '=', $ausgabe_id)
		// -> whereIn('beitraege.status_id', array(5, 6, 7))
		-> orderBy('kategorien.reihenfolge') -> orderBy('ausgaben_beitraege.reihenfolge','desc') -> orderBy('ausgaben_beitraege.beitrag_id','desc') -> select(array('beitraege.*', 'kategorien.id as k')) -> get();

		$rubriken = DB::table('kategorien') -> where('mandant_id', '=', $this -> mandant -> id) -> orderBy('reihenfolge') -> select(array('id', 'bezeichnung', 'hauptkategorie', 'tiefe', 'xml_tag', 'eps_id', 'export_always', 'headline_style', 'untertitel_style', 'absatz_style')) -> get();

		$kats = [];
		$kb = [];

		foreach ($rubriken as $kat) {
			$id = $kat -> id;
			$kats[$id] = $kat;
			$kb[$id] = [];

			$kat -> show = $kat -> export_always;
		}
		foreach ($beitraege as $b) {
			$kats[$b -> k] -> show = 1;
			$kb[$b -> k][] = $b;
		}

		$iterate = true;
		while ($iterate) {
			$iterate = false;
			foreach ($rubriken as $kat) {
				if ($kat -> show) {
					if ($id = $kat -> hauptkategorie) {
						if ($kats[$id] -> show == 0) {
							$iterate = true;
							$kats[$id] -> show = 1;
						}
					}
				}
			}
		}

		$content = View::make('export/xml2_m') -> with(['rubriken' => $rubriken, 'beitraege' => $beitraege, 'kb' => $kb]);

		$response = Response::make($content, 302);

		$response -> header('Content-Type', 'text/plain');

		return $response;

		$kats_first = DB::table('kategorien') -> where('mandant_id', '=', $this -> mandant -> id) -> where('tiefe', '=', 1) -> get();

		return $kats;

		$ausgabe -> export_revision += 1;
		$ausgabe -> save();

		$rev = $ausgabe -> export_revision;
		$mandant = $ausgabe -> mandant() -> first();

	}
	public function export_test2($ausgabe,$jahr) {

        //      $ausgabe = 43;
        
        

        if ($this -> user == null || $this -> user -> gruppe_id < 3) {
            return Redirect::to('uebersicht');
        }

        $ausgabe = Ausgabe::where('kw', '=', $ausgabe) -> where('jahr', '=', $jahr) -> where('mandant_id', '=', $this -> mandant -> id) -> first();

        if ($ausgabe == null) {
            // TODO Ausgabe existiert nicht
            return null;
        }

        $ausgabe_id = $ausgabe -> id;
        Session::put('ausgabe', $ausgabe_id);

        /*
         DB::table('ausgaben_export')->insert(
         array('ausgabe_id' => $ausgabe_id)
         );
         */

        //$eps_all = DB::table('eps')->get();

        $beitraege = DB::table('ausgaben_beitraege') -> join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id') -> join('kategorien', 'kategorien.id', '=', 'beitraege.kategorie_id') -> where('ausgaben_beitraege.ausgabe_id', '=', $ausgabe_id)
        // -> whereIn('beitraege.status_id', array(5, 6, 7))
        -> orderBy('kategorien.reihenfolge') -> orderBy('ausgaben_beitraege.reihenfolge','desc') -> orderBy('ausgaben_beitraege.beitrag_id','desc') -> select(array('beitraege.*', 'kategorien.id as k')) -> get();

        $rubriken = DB::table('kategorien') -> where('mandant_id', '=', $this -> mandant -> id) -> orderBy('reihenfolge') -> select(array('id', 'bezeichnung', 'hauptkategorie', 'tiefe', 'xml_tag', 'eps_id', 'export_always', 'headline_style', 'untertitel_style', 'absatz_style')) -> get();

        $kats = [];
        $kb = [];

        foreach ($rubriken as $kat) {
            $id = $kat -> id;
            $kats[$id] = $kat;
            $kb[$id] = [];

            $kat -> show = $kat -> export_always;
        }
        foreach ($beitraege as $b) {
            $kats[$b -> k] -> show = 1;
            $kb[$b -> k][] = $b;
        }

        $iterate = true;
        while ($iterate) {
            $iterate = false;
            foreach ($rubriken as $kat) {
                if ($kat -> show) {
                    if ($id = $kat -> hauptkategorie) {
                        if ($kats[$id] -> show == 0) {
                            $iterate = true;
                            $kats[$id] -> show = 1;
                        }
                    }
                }
            }
        }

        $content = View::make('export/xml') -> with(['rubriken' => $rubriken, 'beitraege' => $beitraege, 'kb' => $kb]);

        $response = Response::make($content, 302);

        $response -> header('Content-Type', 'text/plain');

        return $response;

        $kats_first = DB::table('kategorien') -> where('mandant_id', '=', $this -> mandant -> id) -> where('tiefe', '=', 1) -> get();

        return $kats;

        $ausgabe -> export_revision += 1;
        $ausgabe -> save();

        $rev = $ausgabe -> export_revision;
        $mandant = $ausgabe -> mandant() -> first();

    }
}
