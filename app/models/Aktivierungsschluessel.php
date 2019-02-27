<?php

/**
 * Description of aktivierungsschluessel
 *
 * @author schmid
 */
class Aktivierungsschluessel extends Eloquent {
    protected $table = 'aktivierungsschluessel';
    public static $unguarded = true;
    
    public function user() {
        return $this->belongs_to('User');
    }
}