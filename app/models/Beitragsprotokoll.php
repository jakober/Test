<?php

/*
 *  Feld 
 */

class Beitragsprotokoll extends Eloquent {

    protected $table = 'beitragsprotokoll';
    public static $unguarded = true;

    public function who() {
        return $this->belongsTo('User', 'action_user_id');
    }

    public function beitrag() {
        return $this->belongsTo('Beitrag');
    }

    public function getTextKurz() {

        switch ($this->action_id) {
            case 1:
                return 'erstellt';
            case 2:
                return 'bearbeitet';
            case 3:
                return 'freigegeben';
            case 4:
                return 'mit Änderungen freigegeben';
            case 5:
                return 'abgelehnt';

            default:
                return '(keine Informationen vorhanden)';
        }
    }

    public function getTextLang() {

        $s = $this->getTextKurz() . ' von ' . $this->who()->first()->getFullName();
        if($this->action_id == 5 && $this->nachricht) {
            $s .= ' mit Begründung: ' . $this->nachricht;
        }
        return $s;        
    }

}

?>
