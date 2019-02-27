<?php 

class KategorienKeywords extends Eloquent {
    public static $unguarded = true;
    protected $table = 'kategorien_keywords';
    
    public function kategorie() {
        return $this->belongsTo('Kategorie');
    }

    public function keyword() {
        return $this->belongsTo('Keyword');
    }

}
