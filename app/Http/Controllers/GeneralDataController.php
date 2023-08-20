<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Rules\BattletagInputProhibitCharacters;

use App\Models\Battletag;


class GeneralDataController extends Controller
{
    public function battletagSearch(Request $request){

//return $request->all();
                
        $request->validate([
            'name' => ['userinput', 'string', new BattletagInputProhibitCharacters],
        ]);

        return $request["userinput"];
    }
}
