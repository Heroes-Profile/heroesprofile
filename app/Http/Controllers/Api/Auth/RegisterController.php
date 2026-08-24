<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::guard('api_web')->check()) {
            return redirect('/Api/Account');
        }

        return view('api.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:heroesprofile_api.users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ]);

        $account = ApiAccount::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Not fillable — acceptance is stamped here, never from request input.
        $account->terms_version_accepted = config('api.terms_version');
        $account->terms_accepted_at = now();

        // Straight onto live data. Registration here is only reachable for an email
        // that is not already in the shared `users` table, so an account created
        // this way can never hold an old-site token — `api_tokens` is keyed on a
        // `user_id` that did not exist a moment ago. There is nothing to migrate
        // from, and the gate would only be a button to click for no reason.
        //
        // The gate still does its real job for accounts that predate this site:
        // those keep `migrated = 0` and go through activation, which is what expires
        // their old key.
        $account->migrated = true;

        $account->save();

        // Sends the verification mail. Nothing is gated on the result — see
        // ApiAccount::sendEmailVerificationNotification().
        event(new Registered($account));

        // `migrated` stays 0 — activating live data is a deliberate act in the UI.
        Auth::guard('api_web')->login($account);

        $request->session()->regenerate();

        return redirect('/Api/Account');
    }
}
