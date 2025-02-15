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
        // return response()->json($request->all());

        $data = $request->validate([
            'battletag' => ['required', 'string', new BattletagInputProhibitCharacters],
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        Mail::to('contact@heroesprofile.com')->send(new \App\Mail\ContactFormMail($data));

        return 'success';
    }
}
