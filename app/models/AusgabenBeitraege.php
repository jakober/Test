<?php

class AusgabenBeitraege extends Eloquent {
    public static $unguarded = true;
    protected $table = 'ausgaben_beitraege';
    
    public function user() {
        return $this->belongsTo('User');
    }

    public function ausgabe() {
        return $this->belongsTo('Ausgabe');
    }
}
