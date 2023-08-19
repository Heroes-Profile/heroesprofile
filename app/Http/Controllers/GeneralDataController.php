<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeneralDataController extends Controller
{
    public function battletagSearch(Request $request){
        $userinput = $request["userinput"];



        return $userinput;
    }
}
