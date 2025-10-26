<?php

namespace App\Http\Controllers;

use App\Rules\BattletagInputProhibitCharacters;
use Illuminate\Http\Request;
use Mail;

class ContactController extends Controller
{
    public function show(Request $request)
    {
        return view('contact')->with(['bladeGlobals' => $this->globalDataService->getBladeGlobals()]);
    }

    public function submitMessage(Request $request)
    {
        // Contact form is disabled
        return response()->json(['error' => 'Contact form is temporarily disabled. Please email ZEMILL@heroesprofile.com directly.'], 503);
    }
}
