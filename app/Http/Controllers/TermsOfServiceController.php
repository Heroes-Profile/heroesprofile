<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermsOfServiceController extends Controller
{
    public function show(Request $request)
    {
        return view('termsOfService')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }
}
