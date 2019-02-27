<?php

/**
 * Description of Beitrag
 *
 * @author schmid
 */
class Ausgabe extends Eloquent {
    protected $table = 'ausgaben';
    public static $unguarded = true;

    public function beitraege() {
        return $this->belongsToMany('Beitrag','ausgaben_beitraege');
    }

    public function mandant() {
        return $this->belongsTo('Mandant');
    }

    public function getName() {
        return $this->kw . '/' . $this->jahr;
    }

    public function getDetails() {
        return 'Ausgabe '.$this->kw . '/' . $this->jahr . ' (' . Helpers::fmtDate($this->erschdat) . ')';
    }

    public function scopeNext($query, $mandant_id, $n=1) {
         return $query->where('mandant_id','=',$mandant_id)->where('redschl', '>=', new DateTime())->where('erscheint','=',1)->orderBy('erschdat')->take($n);
    }

    public function scopeNextB($query, $mandant_id, $n=1) {
         return $query->where('mandant_id','=',$mandant_id)->where('erschdat', '>=', new DateTime())->where('erscheint','=',1)->orderBy('erschdat')->take($n);
    }
    public function scopeNextBredschl($query, $mandant_id, $n=1) {
         return $query->where('mandant_id','=',$mandant_id)->where('redschl', '>=', new DateTime())->where('erscheint','=',1)->orderBy('erschdat')->take($n);
    }
    
    public function scopeLastB($query, $mandant_id, $n=1) {
         return $query->where('mandant_id','=',$mandant_id)->where('erschdat', '<=', new DateTime())->where('erscheint','=',1)->orderBy('erschdat','desc')->take($n);
    }
}