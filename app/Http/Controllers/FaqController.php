<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function show(Request $request)
    {
        return view('faq')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }
}
