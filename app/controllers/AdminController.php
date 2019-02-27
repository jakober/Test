<?php

class AdminController extends BaseController {

    public function kategorien() {
        if ($this->user == null) {
            return Redirect::to('/');
        }
        return View::make('rathaus/kategorien');
    }

    public function moveKat() {
        $r = array();
        $r['src'] = Input::get('src');
        $r['dst'] = Input::get('dst');
        $r['method'] = Input::get('method');

        $src = Kategorie::findById(Input::get('src'))->first();
        $dst = Kategorie::findById(Input::get('dst'))->first();

        $check = array();
        $check[$src->hauptkategorie] = 1;
        $check[$dst->hauptkategorie] = 1;

        switch (Input::get('method')) {
            case 'into':
                $check[$dst->id] = 1;
                $src->moveInto($dst);
                break;
            case 'after':
                $src->moveAfter($dst);
                break;
            case 'before':
                $src->moveBefore($dst);
                break;
        }

        Kategorie::refreshCache($this->mandant->id);
        return Response::json($r);
    }

    public function renameCategory() {
        $t = trim(Input::get('text'));
        $id = Input::get('id');
        $k = Kategorie::findById($id)->first();
        if ($t != '') {
            $k->bezeichnung = $t;
            $k->save();
        }
        $r = array('txt' => $k->bezeichnung);
        return Response::json($r);
    }

    public function deleteCategory() {
        $ok = true;
        $id = Input::get('id');
        $k = Kategorie::findById($id)->first();
        $ok = true;
        try {
            DB::transaction(function() use($k, $id) {
                KategorienKeywords::where('kategorie_id', '=', $id)->delete();
                $k->delete();
            });
        } catch (Exception $e) {
            $ok = false;
        }
        $r = array('success' => $ok, 'name' => $k->bezeichnung);
        Kategorie::refreshCache($this->mandant->id);
        return Response::json($r);
    }

    public function newCategory() {
        $name = trim(Input::get('name'));
        $r = array();

        if ($name != '') {
            $mandant_id = Input::get('mandant');
            $k = Kategorie::RootNode($mandant_id)->first();
            $c = $k->sub()->count();

            $nk = Kategorie::create(array(
                        'mandant_id' => $mandant_id,
                        'bezeichnung' => $name,
                        'hauptkategorie' => $k->id,
                        'sortierung' => ($c + 1) * 5,
                        'has_nodes' => false
            ));
            $r['id'] = $nk->id;
        } else {
            $r['err'] = 1;
        }
        Kategorie::refreshCache($this->mandant->id);
        return Response::json($r);
    }

    public function texte($id = null) {
    	
        if ($id != null) {
            $id = str_replace('/', '', $id);
            $filename = storage_path() . '/texte/' . $id . '.txt';
            if (file_exists($filename)) {
                $txt = file_get_contents($filename);
                return view::make('admin/text_edit')->with('text', $txt)->with('type', $id);
				
            } else {
                $id = $id . '_' . $this->mandant->id;
                $filename = storage_path() . '/texte/' . $id . '.txt';
                if (file_exists($filename)) {
                    $txt = file_get_contents($filename);
                    return view::make('admin/text_edit')->with('text', $txt)->with('type', $id);

                }
            }
        }
        return View::make('admin/texte');
    }

    public function text_save() {
        if ($this->user == null || $this->user->gruppe_id != 3) {
            return Redirect::to('uebersicht');
        }
        $filename = storage_path() . '/texte/' . Input::get('type') . '.txt';
        file_put_contents($filename, Input::get('content'));
        return View::make('admin/texte')->with('flash', 'Text wurde gespeichert.');
    }

    public function kategorien2() {
    	
		$verzeichnis = public_path() . '/eps/'.$this->mandant->id;
		if ( is_dir ( $verzeichnis ))
		{
			if ( $handle = opendir($verzeichnis) )
   			{
   				while (($file = readdir($handle)) !== false)
        		{
        			$path_parts = pathinfo($file);
					$path_name = $path_parts['dirname'];
					$ext =  $path_parts['extension'];
					$filename =  $path_parts['filename'];
					if($ext=="jpg"){
					$jpgdb = DB::table('eps')->where('filename',$filename)->where('mandant_id',$this->mandant->id)->first();
					if(!$jpgdb){
						
						DB::table('eps')->insert(
						    array('mandant_id' => $this->mandant->id, 'filename' => $filename)
						);
						
					}
					}
					
				}
				closedir($handle);
			}
		}
		


        $rubriken = Kategorie::where('mandant_id','=',$this->mandant->id)->where('tiefe','>',0)->orderBy('reihenfolge')->get();
        $eps_all = DB::table('eps')->where('mandant_id','=',$this->mandant->id)->orderBy('filename')->get();
        return View::make('admin/rubriken')->with('rubriken',$rubriken)->with('eps_all',$eps_all);
    }
}
