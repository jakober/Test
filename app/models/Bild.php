<?php

class Bild extends Eloquent {

    public static $unguarded = true;
    protected $table = 'bilder';

    public function beitrag() {
        return $this->belongsTo('Beitrag');
    }

    public function getExtension() {
        switch ($this->mimetype) {
            case 'image/jpeg':
                return 'jpg';
            case 'image/png':
                return 'png';
            case 'image/gif':
                return 'gif';
        }
    }

    public function getOrigPath() {
        return public_path().'/artikelbilder/' . $this->localfilename;
    }

    public function getOrigLink() {
        return '/artikelbilder/' . $this->localfilename;
    }

    public function getMediumPath() {
        return public_path().'/artikelbilder/medium/' . $this->localfilename;
    }

    public function getMediumLink() {
        return '/artikelbilder/medium/' . $this->localfilename;
    }

    public function getThumbPath() {
        return public_path().'/artikelbilder/thumb/' . $this->localfilename;
    }

    public function getThumbLink() {
        return '/artikelbilder/thumb/' . $this->localfilename;
    }

    public function getExportFileName() {
		$filename = $this->filename;
		$filename = str_replace('Ä','Ae',$filename);
		$filename = str_replace('Ö','Oe',$filename);
		$filename = str_replace('Ü','Ue',$filename);
		$filename = str_replace('ß','ss',$filename);
		$filename = str_replace('ä','ae',$filename);
		$filename = str_replace('ö','oe',$filename);
		$filename = str_replace('ü','ue',$filename);
		$filename = str_replace(' ','_',$filename);
		
        return $this->beitrag_id . '_' . $filename;
    }
    
    public function delete() {
        @unlink($this->getThumbPath());
        @unlink($this->getMediumPath());
        @unlink($this->getOrigPath());
        parent::delete();
    }

}
