<?php

/**
 * Description of Helpers
 *
 * @author schmid
 */
class Helpers {

    public static function wochentag($d) {
        $wt = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
        return $wt[$d];
    }
    
    public static function wochentag_lang($d) {
        $wt = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
        return $wt[$d];
    }
    
    public static function fmtDateTime($date) {
        if(is_string($date)) {
            $date = new DateTime($date);
        }
        return $date->format('d.m.Y H:i:s');
    }

    public static function fmtDate($date) {
        if(is_string($date)) {
            $date = new DateTime($date);
        }
        return $date->format('d.m.Y');
    }

    public static function tinyMCE() {
        $dev = false;
        $script = $dev ? '/js/tinymce_dev/js/tinymce/tinymce.js' : '/js/tinymce/tinymce.min.js';
        return '<script type="text/javascript" src="'.$script.'"></script>';
    }
}
