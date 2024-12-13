<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnimationsController extends Controller
{
    public function showDeathwing(Request $request)
    {
        return view('Animations.deathwing')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }

    public function showTassadar(Request $request)
    {
        return view('Animations.tassadar')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }
}
