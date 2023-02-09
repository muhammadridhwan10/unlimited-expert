<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationController extends Controller
{

    public function index()
    {
        return view('translate.index');
    }


    public function translate(Request $request)
    {
        $lang_one = $request->lang_one;
        $lang_two = $request->lang_two;
        $text = $request->text;

        if ($lang_one == "AUTO_DETECT") {
            $tr = new GoogleTranslate($lang_two);

            $text = $tr->translate($text);

            $lang_one = $tr->getLastDetectedSource();

            echo GoogleTranslate::trans($text, $lang_two, $lang_one);
        } else {
            echo GoogleTranslate::trans($text, $lang_two, $lang_one);
        }
    }
}
