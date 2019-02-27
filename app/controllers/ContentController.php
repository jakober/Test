<?php

class ContentController extends BaseController {
    
    public function kurzeinweisung() {
        return View::make('content/kurzeinweisung');
    }
}