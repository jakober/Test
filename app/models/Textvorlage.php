<?php

class Textvorlage extends Eloquent {

    protected $table = 'textvorlagen';
    public static $unguarded = true;

    public function user() {
        return $this->belongsTo('User');
    }

    public function kategorie() {
        return $this->belongsTo('Kategorie');
    }

}

?>
