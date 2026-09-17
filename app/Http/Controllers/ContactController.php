<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
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

        // Always verified where reCAPTCHA is set up. Leaving the token out used to skip the
        // check entirely, which is exactly what a bot posting directly does. Unconfigured
        // (local development) it is skipped, since verify() would fail every request.
        if (config('services.recaptcha.secret_key')) {
            $recaptchaToken = (string) $request->input('recaptcha_token');
            $recaptchaResult = $recaptchaToken === ''
                ? ['success' => false]
                : $recaptchaService->verify($recaptchaToken, 'contact_form', $request);

            if (! $recaptchaResult['success']) {
                return response()->json(['error' => 'recaptcha_failed'], 400);
            }
        }

        $data = $request->validate([
            'battletag' => ['required', 'string', new BattletagInputProhibitCharacters],
            'email' => 'required|email',
            'message' => 'required|string',
            'website' => 'nullable|string', // Honeypot field - should be empty
            'recaptcha_token' => 'nullable|string', // reCAPTCHA token
        ]);

        Mail::to('contact@heroesprofile.com')->send(new ContactFormMail($data));

        return 'success';
    }
}
