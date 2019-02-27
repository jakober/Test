<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

/**
 * Description of Beitrag
 *
 * @author schmid
 */
class User extends Eloquent implements UserInterface, RemindableInterface {

    protected $table = 'users';
    public static $unguarded = true;

    public function beitraege() {
        return $this->hasMany('Beitrag');
    }

    public function entwuerfe() {
        return $this->hasMany('Beitrag')->where('status_id','=',1);
    }

    public function gruppe() {
        return $this->belongsTo('Gruppe');
    }

    public function mandant() {
        return $this->belongsTo('Mandant');
    }

    public function kategorien() {
        return $this->belongsToMany('Kategorie', 'user_kategorien', 'user_id', 'kategorie_id');
    }

    public function aktivierungsschluessel() {
        return $this->hasOne('Aktivierungsschluessel');
    }

    public function key() {
        return $this->hasOne('Aktivierungsschluessel');
    }

    public function textvorlagen() {
        return $this->hasMany('Textvorlage');
    }

    public function isAdmin() {
        return $this->gruppe_id>1;
    }

    public function getAnredeKurz() {
        switch($this->anrede) {
            case 'm':
                return 'Herr ' . $this->name;
            case 'w':
                return 'Frau ' . $this->name;
            case 'f':
                return 'Firma ' . $this->firma;
        }
    }

    public function getAnredeSehrGeehrte() {
        switch($this->anrede) {
            case 'm':
                return 'Sehr geehrter Herr ' . $this->name;
            case 'w':
                return 'Sehr geehrte Frau ' . $this->name;
            case 'f':
                return 'Sehr geehrte Firma ' . $this->firma;

        }
    }

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password');

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier() {
        //return $this->getKey();
        return $this->username;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword() {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail() {
        return $this->email;
    }

    public function getFullName() {
        return $this->vorname . ' ' . $this->name;
    }

}