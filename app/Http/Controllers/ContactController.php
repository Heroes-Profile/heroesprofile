<?php

namespace App\Http\Controllers;

use App\Rules\BattletagInputProhibitCharacters;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Mail;

class ContactController extends Controller
{
    public function show(Request $request)
    {
        return view('contact')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }

    public function submitMessage(Request $request, RecaptchaService $recaptchaService)
    {
        // return response()->json($request->all());

        // Honeypot validation - if website field is filled, it's likely a bot
        if (! empty($request->input('website'))) {
            // Silently reject the submission (don't reveal it's a honeypot)
            return 'success';
        }

        // Validate reCAPTCHA
        $recaptchaToken = $request->input('recaptcha_token');
        if ($recaptchaToken) {
            $recaptchaResult = $recaptchaService->verify($recaptchaToken, 'contact_form');
            if (! $recaptchaResult['success']) {
                return response()->json(['error' => 'reCAPTCHA verification failed'], 400);
            }
        }

        $data = $request->validate([
            'battletag' => ['required', 'string', new BattletagInputProhibitCharacters],
            'email' => 'required|email',
            'message' => 'required|string',
            'website' => 'nullable|string', // Honeypot field - should be empty
            'recaptcha_token' => 'nullable|string', // reCAPTCHA token
        ]);

        Mail::to('contact@heroesprofile.com')->send(new \App\Mail\ContactFormMail($data));

        return 'success';
    }
}
