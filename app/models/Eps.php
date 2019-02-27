<?php

class Eps extends Eloquent {

    public static $unguarded = true;
    protected $table = 'eps';

    public function getFile() {
        return storage_path() . '/eps/' . $this->id . '.eps';
    }

    public function getPngFile() {
        return public_path() . '/eps/' . $this->id . '.png';
    }

    public function createPng() {
        //$r = exec($s='convert ' . $this->getFile() . ' ' . $this->getPngFile(),$out,$ret);
        exec('convert /tmp/test.eps /tmp/test.png');
        exec('convert /tmp/picture.png /tmp/picture_copy.png');

        //echo $s;
        //$r = exec($s='/usr/bin/convert',$out,$ret);
        //echo $s;
        echo file_exists($this->getPngFile()) or die('error2');
        print_r($out);
        print_r($ret);
    }
}
