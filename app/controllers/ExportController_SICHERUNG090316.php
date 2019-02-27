<?php

class ExportController extends BaseController {

	public function tree() {
		Return View::make('export/tree');
	}

	public function uebersicht() {
		$ausgaben = Ausgabe::nextB($this -> mandant -> id, 6) -> get();
		Return View::make('export/uebersicht') -> with('ausgaben', $ausgaben);
	}

	public function export($ausgabe, $jahr) {

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
        
		$eps_all = DB::table('eps') -> get();
		$beitraege = DB::table('ausgaben_beitraege') -> join('beitraege', 'ausgaben_beitraege.beitrag_id', '=', 'beitraege.id') -> join('kategorien', 'kategorien.id', '=', 'beitraege.kategorie_id') -> where('ausgaben_beitraege.ausgabe_id', '=', $ausgabe_id) -> whereIn('beitraege.status_id', array(5, 6, 7)) -> orderBy('kategorien.reihenfolge') -> select(array('beitraege.id')) -> get();

		$a = array();
		foreach ($beitraege as $b) {
			$a[] = $b -> id;
		}

		$beitraege = Beitrag::whereIn('id', $a) -> get();

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

		$zip -> addEmptyDir($zipfilename . '/bilder');
		foreach ($beitraege as $b) {
			$bilder = $b -> bilder() -> get();
			if (count($bilder)) {
				$zip -> addEmptyDir($dirname1 = $zipfilename . '/bilder/' . $b -> id);
				$zip -> addEmptyDir($dirname2 = $zipfilename . '/originalbilder/' . $b -> id);
				$dirname1 .= '/';
				$dirname2 .= '/';
			}
			
			
			foreach ($bilder as $bild) {
				if (file_exists($origPath = $bild -> getOrigPath())) {
					$zip -> addFile($origPath, $dirname2 . $bild -> getExportFileName());
	                $resizer = Resizer::open($origPath);

                    $tmpfile = storage_path() . '/tmp/' . Str::random(20) . '.' . strtolower(File::extension($origPath));

    	            $resizer->resize(164, null, 'landscape')->save($tmpfile);
					$zip -> addFile($tmpfile, $dirname1 . $bild -> getExportFileName());
					
					
				}
			}
		}

		// $zip->addEmptyDir($zipfilename . '/eps');
		// $eps_all = DB::table('eps')->get();
		//
		//
		// foreach ($eps_all as $eps) {
		//
		// $epspfad = public_path().'/eps/'.$eps->filename.'.eps';
		//
		// if (file_exists($epspfad)) {
		// $zip->addFile($epspfad, $zipfilename . '/eps/'.$eps->filename.'.eps');
		// }
		//
		// }

		$rubriken = DB::table('kategorien') -> where('mandant_id', '=', $this -> mandant -> id) -> orderBy('reihenfolge') -> select(array('id', 'bezeichnung', 'hauptkategorie', 'tiefe', 'xml_tag', 'eps_id', 'export_always')) -> get();

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
        
        foreach ($beitraege as $o) {
         $status_neu = 7;
         DB::table('beitraege')
            ->where('id', $o->id)
            ->update(array('status_id' => $status_neu));
         
         }
        
        
		$content = View::make('export/xml') -> with(['rubriken' => $rubriken, 'beitraege' => $beitraege, 'kb' => $kb]);

		$zip -> addFromString($zipfilename . '/inhalt.xml', $content);
  
		$zip -> close();
		

		//return Response::make($view, 200, array('Content-Type' => 'text/xml'));
		
		return Response::download($dir . '.zip');
	}

	public function export_test($ausgabe) {

		//		$ausgabe = 43;
		$jahr = 2015;

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
		-> orderBy('kategorien.reihenfolge') -> select(array('beitraege.*', 'kategorien.id as k')) -> get();

		$rubriken = DB::table('kategorien') -> where('mandant_id', '=', $this -> mandant -> id) -> orderBy('reihenfolge') -> select(array('id', 'bezeichnung', 'hauptkategorie', 'tiefe', 'xml_tag', 'eps_id', 'export_always')) -> get();

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
