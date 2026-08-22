<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
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
        $account->save();

        // `migrated` stays 0 — activating live data is a deliberate act in the UI.
        Auth::guard('api_web')->login($account);

        $request->session()->regenerate();

        return redirect('/Api/Account');
    }
}
