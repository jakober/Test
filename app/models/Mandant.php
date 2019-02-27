<?php

/**
 * Description of Beitrag
 *
 * @author schmid
 */
class Mandant extends Eloquent {

    protected $table = 'mandanten';
    public static $unguarded = true;

    /*
      public function user() {
      return $this->belongsToMany('User', 'user_mandanten', 'mandant_id', 'user_id');
      }
     *
     */

    public function user() {
        return $this->hasMany('User');
    }

    public function ausgaben() {
        return $this->hasMany('Ausgabe');
    }

    public function kategorien() {
        return $this->hasMany('Kategorie');
    }


}