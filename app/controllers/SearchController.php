<?php

class SearchController extends BaseController {

    public function sk() {
        $mandant_id = $this->mandant->id;
        $kw = trim(str_replace('%', '', Input::get('kw')));
  
        $a = array();
            $kws = preg_split('/[\s,]+/', trim(Input::get('kw')));

            $query1 = DB::table('kategorien');
            $c = 1;
            foreach ($kws as $k) {
                $query1 = $query1->join('kategorien_keywords as k' . $c, 'k' . $c . '.kategorie_id', '=', 'kategorien.id')
                                ->join('keywords as kw' . $c, 'k' . $c . '.keyword_id', '=', 'kw' . $c . '.id')
                                ->where('kw' . $c . '.mandant_id', '=', $mandant_id)
                                ->where('kw' . $c . '.keyword', 'LIKE', $k . '%')->select(array('kategorien.id'));
                $c++;
            }

            $result = DB::table('kategorien')
                            ->where('mandant_id', '=', $mandant_id)
                            ->where('bezeichnung', 'LIKE', '%' . $kw . '%')
                            ->union($query1)
                            ->select(array('kategorien.id'))->distinct()->get();

            foreach ($result as $i) {
                $a[] = intval($i->id);
            }
       
        return array('ids' => $a);
    }




    public function skAll() {
        $mandant_id = $this->mandant->id;
        $kw = trim(str_replace('%', '', Input::get('kw')));


        $a = array();
      
            $kw = " ";
            $kws = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        
            $query1 = DB::table('kategorien');
            $c = 1;
            
                
                
                $query1 = DB::table('kategorien')
                        ->where('mandant_id', '=', $mandant_id)
                        ->select(array('kategorien.id'));
                
    
 
            
      

            $result = DB::table('kategorien')
                            ->where('mandant_id', '=', $mandant_id)
                            ->where('bezeichnung', 'LIKE', '%' . $kw . '%')
                            ->union($query1)
                            ->select(array('kategorien.id'))->distinct()->get();
        
        
            // $kws = [];
            // $kws[] = "";
            
            
            foreach ($result as $i) {
                $a[] = intval($i->id);
            }
       
        return array('ids' => $a);
    }





    /*
      public function sk() {
      $kw = trim(str_replace('%', '', Input::get('kw')));
      $a = array();
      if (strlen($kw) > 1) {
      $result = DB::table('kategorien')
      ->join('kategorien_keywords', 'kategorien_keywords.kategorie_id', '=', 'kategorien.id')
      ->join('keywords', 'kategorien_keywords.keyword_id', '=', 'keywords.id')
      ->where('keyword', 'LIKE', $kw . '%')
      ->orderBy('kategorien.bezeichnung')
      ->select(array('kategorien.id'))->distinct()->get();

      foreach ($result as $i) {
      $a[] = intval($i->id);
      }
      }
      return array('ids'=>$a);
      }
     */
}


    








