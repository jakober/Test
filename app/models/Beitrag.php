<?php

class Beitrag extends Eloquent {

    public static $unguarded = true;

    protected $table = 'beitraege';

    public function ausgaben() {
        return $this->belongsToMany('Ausgabe', 'ausgaben_beitraege', 'beitrag_id', 'ausgabe_id');
    }

    public function user() {
        return $this->belongsTo('User');
    }

    public function kategorie() {
        return $this->belongsTo('Kategorie');
    }

    public function bilder() {
        return $this->hasMany('Bild');
    }
     public function eps() {
        return $this->hasMany('eps');
    }

    public function anhaenge() {
        return $this->hasMany('Anhang');
    }

    public function status() {
        return $this->belongsTo('Status');
    }

    public function isEditable() {
        return $this->status_id<3;
    }

    public function beitragsprotokolle() {
        return $this->hasMany('Beitragsprotokoll');
    }

    public function delete() {
        $bilder =$this->bilder()->get();
        foreach($bilder as $bild) {
            $bild->delete();
        }

        $anhaenge = $this->anhaenge()->get();
        foreach($anhaenge as $anhang) {
            $anhang->delete();
        }

        AusgabenBeitraege::where('beitrag_id','=',$this->beitrag_id)->delete();
        $this->beitragsprotokolle()->delete();
        parent::delete();
    }

    public function canBeEditedBy($user) {
        $katId = $this->kategorie_id;
        return $user->gruppe_id == 2 || UserKategorien::where('kategorie_id','=',$katId)->where('user_id','=',$user->id)->count();
    }
}