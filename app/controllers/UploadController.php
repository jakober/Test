<?php

/**
 * Description of UploadController
 *
 * @author schmid
 */
class UploadController extends Controller {

    public function upload() {

        if (Input::hasFile('file')) {
            $file = Input::file('file');

            $bid = Input::get('beitrag_id');
            $mime = $file->getMimeType();
			
            if (substr($mime, 0, 6) == 'image/' && substr($mime, 6, 3) != 'svg') {
                $info = getimagesize($file);
                $end = strtolower(File::extension($file->getClientOriginalName()));
                //$end = 'jpg';
                if (!in_array($end, array('jpg', 'jpeg', 'png', 'gif'))) {
                    return Response::json(array('error' => 2));
                }
                do {
                    $localfilename = Str::random(20) . '.' . $end;
                    //$dstO = storage_path() . '/artikelbilder/orig/' . $bid . '/' . $localfilename;
                    //$dstT = storage_path() . '/artikelbilder/thumb/' . $bid . '/' . $localfilename;
                    $dstT = public_path() . '/artikelbilder/thumb/' . $localfilename;
                } while (file_exists($dstT));
                //Bundle::start('resizer');

                $resizer = Resizer::open($file);

                $resizer->resize(1024, 800, 'auto')->save(public_path() . '/artikelbilder/medium/' . $localfilename);
                $ww = $resizer->thumb_width;
                $wh = $resizer->thumb_height;

                $resizer->resize(220, 110, 'fit')
                        ->save($dstT);

                $bild = Bild::create(array(
                        'beitrag_id' => $bid,
                        'filename' => $file->getClientOriginalName(),
                        'localfilename' => $localfilename,
                        'mimetype' => image_type_to_mime_type($info[2]),
                        'bildunterschrift' => '',
                        'w' => $resizer->width,
                        'h' => $resizer->height,
                        'ww' => $ww,
                        'wh' => $wh,
                        'tw' => $resizer->thumb_width,
                        'th' => $resizer->thumb_height
                ));

                Input::file('file')->move(public_path() . '/artikelbilder', $localfilename);

                $data = array();
                //$data['beitrag_id'] = $bid;
                $data['id'] = $bild->id;
                $data['w'] = $resizer->thumb_width;
                $data['h'] = $resizer->thumb_height;
                $data['srcT'] = $bild->getThumbLink();
                $data['srcO'] = $bild->getMediumLink();
                $data['img'] = true;
                $data['mime'] = $mime;
                return $data;
            } else {
            	
				$end = strtolower(File::extension($file->getClientOriginalName()));
				
				if(Session::get('mandant_id') == 3){
					if (!in_array($end, array('pdf','doc','docx'))) {
						return array('error'=>99);
					}	
				}else{
					if (!in_array($end, array('pdf','doc','docx'))) {
						return array('error'=>99);
					}	
				}
				
				
				
                $path = Storage_Path() . '/anhaenge/' . $bid;
                if (!file_exists($path)) {
                    mkdir($path);
                }
				$name = str_replace(" ", "_", $this->sonderzeichen($file->getClientOriginalName()));
                $size = $file->getClientSize();
                $mime = $file->getMimeType();
                //if (!file_exists($path . '/' . $name)) {

                $file->move($path, $name);
                $anhang = Anhang::create(array(
                            'beitrag_id' => $bid,
                            'filename' => $name,
                            'size' => $size,
                            'mimetype' => $mime
                ));
                return array('mime' => $mime, 'filename' => $name, 'size' => $size, 'href' => $anhang->getURL(), 'id'=>$anhang->id);
                //} else {
                //    return array('error' => 13);
                //}
            }
        } else {
            return array('error'=>99);
        }
    }
    
    
	public function sonderzeichen($string)
	{
		$string = str_replace("ä", "ae", $string);
		$string = str_replace("ü", "ue", $string);
		$string = str_replace("ö", "oe", $string);
		$string = str_replace("Ä", "Ae", $string);
		$string = str_replace("Ü", "Ue", $string);
		$string = str_replace("Ö", "Oe", $string);
		$string = str_replace("ß", "ss", $string);
		$string = str_replace("´", "", $string);
		return $string;
	}
	
    public function download() {
        $id = Input::get('id');
        $anhang = Anhang::find($id);
        return Response::download($anhang->getPath());
    }

}

?>
