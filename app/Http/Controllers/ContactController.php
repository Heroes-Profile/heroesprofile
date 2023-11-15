<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Rules\BattletagInputProhibitCharacters;

class ContactController extends Controller
{
    public function show(Request $request)
    {
        return view('contact');
    }

    public function submitMessage(Request $request){
        //return response()->json($request->all());

        $data = $request->validate([
            'battletag' => ['required', 'string', new BattletagInputProhibitCharacters],
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        Mail::to('zemill@heroesprofile.com')->send(new \App\Mail\ContactFormMail($data));
        return response()->json(['message' => 'Email sent successfully']);
    }
}
