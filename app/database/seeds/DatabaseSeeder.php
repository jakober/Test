<?php

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Eloquent::unguard();

        $this->call('StatusTableSeeder');
        $this->call('GruppenTableSeeder');

        $this->call('MandantTableSeeder');

        $this->call('KategorieTableSeeder');
        $this->call('UserTableSeeder');
        $this->call('AusgabeTableSeeder');

//        $this->call('TextvorlagenTableSeeder');
    }

}

class StatusTableSeeder extends Seeder {

    public function run() {
        Status::create(
                array(
                    'id' => 0,
                    'bezeichnung' => 'temporär'
                )
        );
        Status::create(
                array(
                    'id' => 1,
                    'bezeichnung' => 'Entwurf',
                    'bild' => 'led-grau.png'
                )
        );
        Status::create(
                array(
                    'id' => 2,
                    'bezeichnung' => 'Neuer Beitrag',
                    'bild' => 'led-gelb.png'
                )
        );
        Status::create(
                array(
                    'id' => 3,
                    'bezeichnung' => 'gelesen',
                    'bild' => 'led-blau.png'
                )
        );
        Status::create(
                array(
                    'id' => 4,
                    'bezeichnung' => 'abgelehnt',
                    'bild' => 'led-rot.png'
                )
        );

        Status::create(
                array(
                    'id' => 5,
                    'bezeichnung' => 'freigegeben',
                    'bild' => 'led-gruen.png'
                )
        );

        Status::create(
                array(
                    'id' => 6,
                    'bezeichnung' => 'freigegeben + online',
                    'bild' => 'led-gruen.png'
                )
        );

        Status::create(
                array(
                    'id' => 7,
                    'bezeichnung' => 'exportiert',
                    'bild' => 'led-gruen.png'
                )
        );
    }

}

class GruppenTableSeeder extends Seeder {

    public function run() {
        Gruppe::create(
                array(
                    'id' => 1,
                    'bezeichnung' => 'Redakteure'
                )
        );
        Gruppe::create(
                array(
                    'id' => 2,
                    'bezeichnung' => 'Rathaus-Admin'
                )
        );
        Gruppe::create(
                array(
                    'id' => 3,
                    'bezeichnung' => 'Bairle-Admin'
                )
        );
    }

}

class KategorieTableSeeder extends Seeder {

    public function run() {
        foreach (range(1, 4) as $mandant_id) {
            $src = file_get_contents(dirname(__FILE__) . '/kategorien_' . $mandant_id);
            $src = str_replace("\r", ' ', $src);
            $arr = explode("\n", $src);
            $parent = array(null);
            $counters = array(0);
            $lastIndent = 0;

            $indentLengths = array(0);

            foreach ($arr as $name) {
                $name = rtrim($name);

                $name_plain = ltrim($name);
                if ($name_plain == '') {
                    continue;
                }

                $j = strlen($name) - strlen($name_plain);

                if ($j > $lastIndent) {
                    array_push($indentLengths, $j);
                } else if ($j < $lastIndent) {
                    do {
                        $l = array_pop($indentLengths);
                        array_pop($counters);
                        array_pop($parent);
                    } while (count($indentLengths) > 0 && $l > $j);
                    array_push($indentLengths, $j);
                }

                $lastIndent = $j;
                $depth = count($indentLengths) - 1;

                $name_ = substr($name, $j);
                $posD = strpos($name_, ':');
                if ($posD == false) {
                    $name = $name_;
                    $kws = '';
                } else {
                    $name = substr($name_, 0, $posD);
                    $kws = trim(substr($name_, $posD + 1));
                }

                $counters[$depth]++;

                $counters[$depth + 1] = 0;

                // echo $depth, "\n";
                //echo implode(',', $indentLengths), "\n";
                //echo implode(',', $counters), "\n";
                //echo implode(',', $parent), "\n";
                //echo $id, ' ', $name, "\n\n";

                if ($name != '') {
                    $k = Kategorie::create(
                                    array(
                                        'mandant_id' => $mandant_id,
                                        'bezeichnung' => $name,
                                        'sortierung' => $counters[$depth] * 5,
                                        'hauptkategorie' => $parent[$depth],
                                        'tiefe' => $depth
                                    )
                    );
                    $id = $k->id;

                    if ($kws != '') {
                        $list = explode(',', $kws);
                        array_walk($list, function(&$value) {
                                    $value = trim($value);
                                });
                        if ($k != null) {
                            $k->setKeywords($list);
                        }
                    }
                    $parent[$depth + 1] = $id;
                }
            }
            $ids = DB::table('kategorien')->where('hauptkategorie', '!=', null)->where('mandant_id', '=', $mandant_id)->select('hauptkategorie as k')->distinct()->get();
            $a = array();
            foreach ($ids as $o) {
                $a[] = $o->k;
            }
            if (count($a)) {
                DB::table('kategorien')->whereIn('id', $a)->update(array('has_nodes' => 1));
            }
            Kategorie::refreshCache($mandant_id);
        }
    }

}

class MandantTableSeeder extends Seeder {

    public function run() {
        //DB::table('mandanten')->delete();
        $post = '.rs4';
        Mandant::create(
                array(
                    'id' => 1,
                    'bezeichnung' => 'NRBL Dischingen',
                    'logo' => 'head-dischingen.jpg',
                    'hostname' => 'dischingen' . $post,
                    'email_footer' => 'Freundliche Grüße<br /><br />Ihr Redaktionsteam Dischingen'
                )
        );
        Mandant::create(
                array(
                    'id' => 2,
                    'bezeichnung' => 'NRBL Neresheim',
                    'logo' => 'head-neresheim.jpg',
                    'hostname' => 'neresheim' . $post,
                    'email_footer' => 'Freundliche Grüße<br /><br />Ihr Redaktionsteam Neresheim'
                )
        );
        Mandant::create(
                array(
                    'id' => 3,
                    'bezeichnung' => 'Amtsblatt Syrgenstein',
                    'logo' => 'head-syrgenstein.jpg',
                    'hostname' => 'vg-syrgenstein' . $post,
                    'email_footer' => 'Freundliche Grüße<br /><br />Ihr Redaktionsteam Syrgenstein'
                )
        );
        Mandant::create(
                array(
                    'id' => 4,
                    'bezeichnung' => 'Demo',
                    'logo' => 'head-neresheim.jpg',
                    'hostname' => 'demo' . $post,
                    'email_footer' => 'Freundliche Grüße<br /><br />Ihr Redaktionsteam Musterstadt'
                )
        );
    }

}

class UserTableSeeder extends Seeder {

    public function run() {
        //DB::table('users')->delete();

        $mandanten = array(1 => 'Dischingen', 2 => 'Neresheim', 3 => 'Syrgenstein', 4 => 'Demo');
        foreach (array(1, 2, 3, 4) as $mandant_id) {
            if ($mandant_id == 4) {
                User::create(
                        array(
                            'mandant_id' => $mandant_id,
                            'gruppe_id' => 1,
                            'username' => 'schmid',
                            'password' => Hash::make('schmid'),
                            'email' => 'schmid@bairle.de',
                            'anrede' => 'm',
                            'name' => 'Schmid',
                            'vorname' => 'Egon',
                            'aktiviert' => 1,
                            'freigeschaltet' => 1
                        )
                );
                User::create(
                        array(
                            'mandant_id' => $mandant_id,
                            'gruppe_id' => 1,
                            'username' => 'tbairle',
                            'password' => Hash::make('bairle'),
                            'email' => 'tbairle@bairle.de',
                            'anrede' => 'm',
                            'name' => 'Bairle',
                            'vorname' => 'Tobias',
                            'aktiviert' => 1,
                            'freigeschaltet' => 1
                        )
                );
            }
            User::create(
                    array(
                        'mandant_id' => $mandant_id,
                        'gruppe_id' => 2,
                        'username' => 'rathaus',
                        'password' => Hash::make('rathaus'),
                        'email' => 'info@bairle.de',
                        'anrede' => 'm',
                        'name' => $mandanten[$mandant_id],
                        'vorname' => 'Rathaus',
                        'aktiviert' => 1,
                        'freigeschaltet' => 1
                    )
            );
            User::create(
                    array(
                        'mandant_id' => $mandant_id,
                        'gruppe_id' => 3,
                        'username' => 'bairle',
                        'password' => Hash::make('toll3000'),
                        'email' => 'hirschbolz@bairle.de',
                        'anrede' => 'm',
                        'name' => 'Hirschbolz',
                        'vorname' => 'Franz-Josef',
                        'aktiviert' => 1,
                        'freigeschaltet' => 1
                    )
            );
        }

        /*
          foreach (array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11) as $i) {
          UserKategorien::create(
          array(
          'user_id' => 1,
          'kategorie_id' => $i,
          'aktiv' => 1
          )
          );
          }

          User::create(
          array(
          'id' => 2,
          'mandant_id' => 1,
          'gruppe_id' => 3,
          'username' => 'bairle',
          'password' => Hash::make('bairle'),
          'email' => 'tbairle@bairle.de',
          'name' => 'Bairle',
          'vorname' => 'Tobias',
          'aktiviert' => 1,
          'freigeschaltet' => 1
          )
          );

          foreach (array(7, 8, 9, 10, 11, 12, 13, 14, 15, 16) as $i) {
          UserKategorien::create(
          array(
          'user_id' => 2,
          'kategorie_id' => $i,
          'aktiv' => 1
          )
          );
          }

          User::create(
          array(
          'id' => 3,
          'mandant_id' => 1,
          'gruppe_id' => 2,
          'username' => 'rathaus',
          'password' => Hash::make('rathaus'),
          'email' => 'info@bairle.de',
          'name' => 'Dischingen',
          'vorname' => 'Rathaus',
          'aktiviert' => 1,
          'freigeschaltet' => 1
          )
          );

          User::create(
          array(
          'id' => 4,
          'mandant_id' => 1,
          'gruppe_id' => 1,
          'username' => 'nobody',
          'password' => Hash::make('nobody'),
          'email' => 'tbairle@bairle.de',
          'name' => 'Nobody',
          'vorname' => 'Nobody',
          'aktiviert' => 1,
          'freigeschaltet' => 1
          )
          );


          User::create(
          array(
          'mandant_id' => 2,
          'gruppe_id' => 1,
          'username' => 'schmid-2',
          'password' => Hash::make('schmid'),
          'email' => 'schmid@bairle.de',
          'name' => 'Schmid',
          'vorname' => 'Egon',
          'aktiviert' => 1,
          'freigeschaltet' => 1
          )
          );

          User::create(
          array(
          'mandant_id' => 3,
          'gruppe_id' => 1,
          'username' => 'schmid-3',
          'password' => Hash::make('schmid'),
          'email' => 'schmid@bairle.de',
          'name' => 'Schmid',
          'vorname' => 'Egon',
          'aktiviert' => 1,
          'freigeschaltet' => 1
          )
          );
         *
         */
    }

}

class AusgabeTableSeeder extends Seeder {

    public function run() {
        foreach (range(1, 4) as $mandant_id) {
            foreach (range(2013, 2015) as $jahr) {
                $this->createAusgaben($mandant_id, $jahr);
            }
        }

        $keine = array("31/2013", "32/2013");

        foreach ($keine as $item) {
            list($kw, $jahr) = explode('/', $item);
            $ausgabe = Ausgabe::where('jahr', '=', $jahr)->where('kw', '=', $kw)->first();
            $ausgabe->erscheint = false;

            $ausgabe->save();
        }
    }

    public function createAusgaben($mandant, $jahr) {
        $f = $this->feiertage($jahr);

        // 1. Montag berechnen
        $tag = new DateTime();
        $tag->setDate($jahr, 1, 1);
        $tag->setTime(0, 0, 0);
        $wt = $tag->format('w');

        if ($wt == 0) {
            $tag->add(new DateInterval('P1D'));
        } else if ($wt > 1) {
            $wt = 8 - $wt;
            $tag->add(new DateInterval("P{$wt}D"));
        }

        do {
            $test = clone $tag;
            $hatFeiertag = false;
            for ($i = 0; $i < 3; $i++) {
                $test->add(new DateInterval('P1D'));
                $hatFeiertag |= in_array($test->format('Y-m-d'), $f);
            }

            $redschl = clone $tag;
            if (!$hatFeiertag) {
                $redschl->add(new DateInterval('P1D'));
            }
            $redschl->add(new DateInterval('PT11H'));

            $erschdat = clone $tag;
            $erschdat->add(new DateInterval('P4D'));

            $tag->add(new DateInterval('P1W'));

            Ausgabe::create(
                    array(
                        'mandant_id' => $mandant,
                        'kw' => $erschdat->format('W'),
                        'jahr' => $erschdat->format('o'),
                        'redschl' => $redschl,
                        'erschdat' => $erschdat,
                        'erscheint' => true
                    )
            );
        } while ($tag->format('Y') == $jahr);
    }

    public function feierTage($jahr) {


        // Feste Feiertage werden nach dem Schema ddmm eingetragen
        $tag = new DateTime();

        $feiertage = array("$jahr-01-01", "$jahr-01-06", "$jahr-05-01", "$jahr-10-03", "$jahr-12-25", "$jahr-12-26");

        // Bewegliche Feiertage berechnen
        $os = easter_date($jahr);
        $offset = $os % 86400;
        if ($offset < 43400) {
            $os -= 86400 - $offset;
        } else if ($offset >= 43200) {
            $os += 86400 - $offset;
        }

        $tag->setTimestamp($os);
        $feiertage[] = $tag->format('Y-m-d');

        $tag->sub(new DateInterval('P2D'));
        $feiertage[] = $tag->format('Y-m-d');

        $tag->add(new DateInterval('P3D'));
        $feiertage[] = $tag->format('Y-m-d');

        $tag->add(new DateInterval('P38D'));
        $feiertage[] = $tag->format('Y-m-d');

        $tag->add(new DateInterval('P10D'));
        $feiertage[] = $tag->format('Y-m-d');

        $tag->add(new DateInterval('P1D'));
        $feiertage[] = $tag->format('Y-m-d');

        $tag->add(new DateInterval('P10D'));
        $feiertage[] = $tag->format('Y-m-d');

        sort($feiertage);

        return $feiertage;
    }

}

