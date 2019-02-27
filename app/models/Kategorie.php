<?php

class Kategorie extends Eloquent {

    protected $table = 'kategorien';
    public static $unguarded = true;

    public function parent() {
        return $this->belongsTo('Kategorie', 'hauptkategorie');
    }

    public function eps() {
        return $this->belongsTo('Eps', 'eps_id');
    }

    public function sub() {
        return $this->hasMany('Kategorie', 'hauptkategorie');
    }

    public function scopeFindById($query, $id) {
        return $query->where('id', '=', $id);
    }

    private function checkSub() {
        $this->has_nodes = ( $this->sub()->count() > 0);
        $this->save();
    }

    public function moveInto($other) {
        $p = $this->parent()->first();

        $this->sortierung = ($other->sub()->count() + 1) * 5;
        $this->hauptkategorie = $other->id;
        $this->save();

        $other->has_nodes = true;
        $other->save();

        $p->checkSub();
    }

    public function moveBefore($other) {
        if ($this->id == $other->id) {
            return;
        }

        $p = $this->parent()->first();

        $this->sortierung = $other->sortierung - 1;
        $this->hauptkategorie = $other->hauptkategorie;
        $this->save();

        $p->checkSub();
    }

    public function moveAfter($other) {
        if ($this->id == $other->id) {
            return;
        }

        $p = $this->parent()->first();

        $this->sortierung = $other->sortierung - 1;
        $this->hauptkategorie = $other->hauptkategorie;
        $this->save();

        $p->checkSub();
    }

    /*
      public function save(array $options = array()) {
      $saved = parent::save($options);
      if ($saved) {
      $p = Kategorie::where('hauptkategorie','=',$this->hauptkategorie)->first();
      //$p = $this->hauptkategorie()->first(); // funktioniert nicht
      if ($p != null && !$p->has_nodes) {
      echo $p->id;
      $p->has_nodes = true;
      $p->save();
      }
      }
      return $saved;
      }
     */

    public function delete() {
        parent::delete();
        $p = Kategorie::find($this->hauptkategorie);
        if ($p != null) {
            $sub = Kategorie::where('hauptkategorie', '=', $this->hauptkategorie)->first();
            if ($sub == null) {
                $p->has_nodes = false;
                $p->save();
            }
        }
    }

    public function keywords() {
        return $this->belongsToMany('Keyword', 'kategorien_keywords');
    }

    public function scopeById($query, $id) {
        return $query->where('id', '=', $id);
    }

    public function setKeywords($list) {
        KategorienKeywords::where('kategorie_id', '=', $this->id)->delete();
        foreach ($list as $item) {
            $kw = Keyword::where('mandant_id', '=', $this->mandant_id)->where('keyword', '=', $item)->first();
            if ($kw == null) {
                $kw = Keyword::create(array(
                            'mandant_id' => $this->mandant_id,
                            'keyword' => $item
                ));
            }
            try {
                KategorienKeywords::create(array(
                    'kategorie_id' => $this->id,
                    'keyword_id' => $kw->id
                ));
            } catch (Exception $e) {

            }
        }
    }

    public function scopeRootNode($query, $mandant_id) {
        return $query->where('mandant_id', '=', $mandant_id)->where('hauptkategorie', '=', null);
    }

    public function users() {
        return $this->belongsToMany('User', 'user_kategorien', 'kategorie_id', 'user_id');
    }

    /**
     *
     * @param array $options
     *
     */
    public function getEpsImage($options) {

    }

    public function getEpsFile() {
        if ($this->eps_file == null) {
            return null;
        }
        return $this->eps()->first()->getFile();
    }

    private static function refreshCache_walkTree($node, &$index, &$depth, $partindex) {

        $node->reihenfolge = $index;
        $node->tiefe = $depth;
        $node->sortierung = $partindex;
        $node->save();

        $index++;
        if ($node->has_nodes) {
            $depth+=1;
            $nodes = Kategorie::where('hauptkategorie', '=', $node->id)->orderBy('sortierung')->get();
            $partindex2 = 5;
            foreach ($nodes as $node2) {
                Kategorie::refreshCache_walkTree($node2, $index, $depth, $partindex2);
                $partindex2+=5;
            }
            $depth-=1;
        }
    }

    public static function refreshCache($mandant_id) {
        $index = 1;
        $depth = 0;
        DB::transaction(function() use(&$index, &$depth, $mandant_id) {
            $root = Kategorie::rootNode($mandant_id)->first();
            Kategorie::refreshCache_walkTree($root, $index, $depth, 5);
        });
    }

    public static function showTree($mandant, $preTree, $postTree, $preNode, $postNode) {
        $root = Kategorie::where('mandant_id', '=', $mandant->id)->where('hauptkategorie', '=', null)->first();
        Kategorie::printTree($root, $preTree, $postTree, $preNode, $postNode);
    }

    public static function printTree($parent, $preTree, $postTree, $preNode, $postNode) {
        $kats = Kategorie::where('hauptkategorie', '=', $parent->id)->orderBy('sortierung')->get();
        if (count($kats) > 0) {
            $preTree($parent);
            foreach ($kats as $kat) {
                $preNode($kat);
                if ($kat->has_nodes) {
                    Kategorie::printTree($kat, $preTree, $postTree, $preNode, $postNode);
                }
                $postNode($kat);
            }
            $postTree($parent);
        }
    }

}
