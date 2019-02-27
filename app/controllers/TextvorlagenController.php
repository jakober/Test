<?php
class TextvorlagenController extends BaseController {

    public function textvorlagen() {
        if ($this->user == null) {
            return Redirect::to('/');
        }
        $tv = $this->user->textvorlagen()->get();

        return View::make('user/textvorlagen')
                        ->with('vorlagen', $tv);
    }

    public function edit($id) {
        return View::make('pages/baustelle')->with('h1','Textvorlage #'.$id.' bearbeiten');
    }
}

?>
