<?php

class Status extends Eloquent {
    protected $table = 'status';
    public static $unguarded = true;

    public function getImagePath() {
        return '/img/status/' . $this->bild;
    }

    public function getImageTag() {
        $s = htmlspecialchars($this->bezeichnung);
        $p = $this->getImagePath();
        return "<img src=\"$p\" alt=\"$s\" title=\"$s\" width=\"24\" height=\"24\" />";
    }
}
