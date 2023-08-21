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
        $request->validate(['userinput' => ['required', 'string', new BattletagInputProhibitCharacters],]);

        return "User input = " . $request["userinput"];
    }
}