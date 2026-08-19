<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function showRequestForm()
    {
        return view('api.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::broker('api_accounts')->sendResetLink($request->only('email'));

        // Same response either way, so this can't be used to enumerate accounts.
        return back()->with('status', __(Password::RESET_LINK_SENT));
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('api.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::broker('api_accounts')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($account, $password) {
                $account->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($account));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        return redirect('/Api/Login')->with('status', __($status));
    }
}
