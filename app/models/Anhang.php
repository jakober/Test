<?php

class Anhang extends Eloquent {

    public static $unguarded = true;
    protected $table = 'anhaenge';

    public function beitrag() {
        return $this->belongsTo('Beitrag');
    }

    public function getURL() {
        return '/dl?id=' . $this->id;
    }

    public function delete() {
        @unlink($this->getPath());
        parent::delete();
    }

    public function getPath() {
        return storage_path() . '/anhaenge/' . $this->beitrag_id . '/' . $this->filename;
    }

    public function getExportFileName() {
        return $this->beitrag_id . '_' . $this->filename;
    }

}