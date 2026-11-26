<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedesignController extends Controller
{
    // The 2026 redesign: what changed, with before and after examples
    public function show(Request $request)
    {
        return view('redesign')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }
}
