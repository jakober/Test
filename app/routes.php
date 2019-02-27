<?php

Route::get('/', 'HomeController@startseite');
Route::get('admin/rubriken','AdminController@kategorien2');
Route::get('admin/texte', 'AdminController@texte');
Route::get('admin/texte/{id}', 'AdminController@texte');
Route::get('ajax/sk', 'SearchController@sk');
Route::get('ajax/skAll', 'SearchController@skAll');
Route::get('artikel/archiv', 'ArtikelController@archiv');
Route::get('artikel/details', 'ArtikelController@details');
Route::get('artikel/kopieren/{vorlage}', 'ArtikelController@kopieren');
Route::get('artikel/neu', 'ArtikelController@neu');
Route::get('artikel/neu/{vorlage}', 'ArtikelController@neuMitVorlage');
Route::get('artikel/textvorlagen', 'TextvorlagenController@textvorlagen');
Route::get('artikel/verwaltung', 'UserController@artikelVerwaltung');
Route::get('artikel/view/{id}', 'ArtikelController@view');
Route::get('auf_wiedersehen', function() { return View::make('home/auf_wiedersehen'); });
Route::get('benutzer/details', 'RathausController@user_details');
Route::get('dl', 'UploadController@download');
Route::get('export/uebersicht','ExportController@uebersicht');
Route::get('export/{kw}_{jahr}/{method}','ExportController@export')->where('method','alle|neue|test|noimage|justimages');
Route::get('impressum', 'HomeController@impressum');
Route::get('kat_beantragen', function() { return Redirect::To('/uebersicht'); });
Route::get('kennwort_aendern', 'UserController@kennwort_vergessen_get');
Route::any('kennwort_vergessen', 'UserController@kennwort_vergessen');
Route::get('kurzeinweisung', 'ContentController@kurzeinweisung');
Route::get('rathaus/archiv', 'RathausController@archiv');
Route::get('rathaus/archiv/{kw}_{jahr}','RathausController@archiv_details');
Route::get('rathaus/ausgaben/{jahr}', 'RathausController@ausgaben');
Route::get('rathaus/beitraege', 'RathausController@beitraege');
Route::get('rathaus/benutzerantraege', 'RathausController@benutzerantraege');
Route::any('rathaus/benutzer', 'RathausController@benutzer');
Route::get('rathaus/benutzer_rubriken/{id}','RathausController@benutzer_rubriken');
Route::get('rathaus/einladungsprotokoll', 'RathausController@einladungsProtokoll');
Route::get('rathaus/manuskript/{kw}_{jahr}','RathausController@manuskript');
Route::get('rathaus/uebersicht', 'RathausController@uebersicht');
Route::get('register2', 'UserController@registrierungsInfo');
Route::get('register', 'UserController@register');
Route::get('registrierung_abschliessen', 'UserController@registrierung_abschliessen');
Route::get('textvorlagen/edit/{id}', 'TextvorlagenController@edit');
Route::get('tree', 'ExportController@tree');
Route::get('uebersicht', 'AppController@uebersicht');
Route::get('user/meine_daten', 'UserController@bearbeiten');
Route::get('user/meine_kategorien', 'UserKategorienController@kategorien');
Route::get('user/iframe', 'UserKategorienController@iframe');
Route::get('user/iframeAusgabe/{frameid}/{key}', 'UserKategorienController@iframeAusgabe');
Route::get('user/iframeAusgabeSingle/{id}/{frameid}', 'UserKategorienController@iframeAusgabeSingle');
Route::get('user/iframeVorschau/{id}/{key}', 'UserKategorienController@iframeVorschau');

Route::get('rathaus/rubriken','AdminController@kategorien');
Route::get('rathaus/changeuser/{userid}', 'RathausController@changeUser');
Route::get('rathaus/deleteuser/{userid}', 'RathausController@deleteUser');

Route::get('rathaus/ausgaben_generieren', 'RathausController@ausgaben_generieren');

Route::get('artikel/artikelvorschau/{id}', 'ArtikelController@artikelvorschau');

Route::post('ajax/generateIframe', 'AjaxController@generateIframe');
Route::post('ajax/generateiframeVorschau', 'AjaxController@generateiframeVorschau');
Route::post('admin/dk', 'AdminController@deleteCategory');
Route::post('admin/mk', 'AdminController@moveKat');
Route::post('admin/nk', 'AdminController@newCategory');
Route::post('admin/rc', 'AdminController@renameCategory');
Route::post('admin/texte', 'AdminController@text_save');
Route::post('ajax/anhang_loeschen', 'AjaxController@anhang_loeschen'); //
Route::post('ajax/as', 'AjaxController@saveArticle');
Route::post('ajax/changeStatus', 'AjaxController@changeStatus');
Route::post('admin/ajax/updateRubriken', 'AjaxController@updateRubriken');
Route::post('ajax/ausgaben', 'AjaxController@ausgaben');
Route::post('ajax/bild_loeschen', 'AjaxController@bild_loeschen'); //
Route::post('ajax/del', 'AjaxController@deleteArticle'); // Artikel löschen
Route::post('ajax/deltv', 'AjaxController@delTV'); // Textvorlage löschen
Route::post('ajax/df', 'RathausController@direktFreigabe');
Route::post('ajax/ga', 'RathausController@gruppeAendern');
Route::post('ajax/bv', 'RathausController@beitragVerschieben');

Route::post('ajax/kb', 'UserKategorienController@neueKategorienHinzufuegen');
Route::post('ajax/rk', 'UserKategorienController@kategorieEntfernen');
Route::post('ajax/st', 'AjaxController@st');
Route::post('artikel/bearbeiten', 'ArtikelController@bearbeiten');
Route::post('kat_beantragen', 'UserKategorienController@beantragen');
Route::post('kennwort_aendern', 'UserController@kennwort_vergessen_post');
Route::post('login', 'UserController@login');
Route::post('logout', 'UserController@logout');
Route::post('rathaus/ajax/as', 'AjaxController@saveArticle_rathaus');
Route::post('rathaus/ajax/ausgabe_details', 'RathausController@ausgabe_details');
Route::post('rathaus/ajax/ausgabeAktiv', 'RathausController@ausgabeAktiv');
Route::post('rathaus/ajax/generateAusgabe', 'RathausController@generateAusgabe');
Route::post('rathaus/ajax/ausgaben_save_detail', 'RathausController@ausgaben_save_detail');
Route::post('rathaus/ajax/ausgabe_status', 'RathausController@ausgabe_status');
Route::post('rathaus/ajax/benutzerkategorien', 'RathausController@benutzerkategorien_speichern');
Route::post('rathaus/ajax/status', 'RathausController@beitrags_status');
Route::post('rathaus/ajax/uf2', 'RathausController@kategorien_uebernehmen');
Route::post('rathaus/ajax/uf', 'RathausController@freigabe');
Route::post('rathaus/artikel_nachbearbeiten', 'ArtikelController@edit_rathaus');

Route::post('register2', 'UserKategorienController@finish');
Route::post('register', 'UserController@register_post');
Route::post('uploadAttachment', 'UploadController@uploadAttachment');
Route::post('upload', 'UploadController@upload');
Route::post('user/meine_daten', 'UserController@bearbeiten_speichern');

Route::get('xxtest/{ausgabe}', 'ExportController@export_test');
Route::get('xxtest2/{ausgabe}/{jahr}', 'ExportController@export_test2');

//Route::get('legende',function() {return View::make('/legende');});
