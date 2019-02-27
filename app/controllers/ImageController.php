<?php

//use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageController extends Controller {

    public function __construct() {
        if (Session::has('user_id')) {
            $this->user = User::find(Session::get('user_id'));
        } else {
            $this->user = null;
        }
    }
/*
    public function getImage($id) {
        $bild = Bild::find($id);
        if(!is_object($bild)||!file_exists($bild->getOrigPath())) {
            App::abort(404);
        }
        
        return new BinaryFileResponse($bild->getOrigPath(), 200, array(
          'Content-Type' => $bild->mimetype,
        ));
    }

    public function getMediumImage($id) {
        $bild = Bild::find($id);
        if(!is_object($bild)||!file_exists($bild->getMedPath())) {
            App::abort(404);
        }
        
        return new BinaryFileResponse($bild->getOrigPath(), 200, array(
          'Content-Type' => $bild->mimetype,
        ));
    }
    public function getThumb($id) {
                
        $bild = Bild::find($id);
        if(!is_object($bild)||!file_exists($bild->getThumbPath())) {
            App::abort(404);
        }

        return new BinaryFileResponse($bild->getThumbPath(), 200, array(
          'Content-Type' => $bild->mimetype,
        ));
    }
*/
}

