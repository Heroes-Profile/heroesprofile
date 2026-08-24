<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\ClientIpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function show(Request $request)
    {
        if (Auth::guard('api_web')->check()) {
            return redirect('/Api/Account');
        }

        return view('api.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request, $credentials['email']);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Try again in '
                    .RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        if (! Auth::guard('api_web')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'Those credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        return redirect()->intended('/Api/Account');
    }

    public function logout(Request $request)
    {
        Auth::guard('api_web')->logout();

        // Not invalidate() — that would also sign them out of the main site.
        $request->session()->regenerateToken();

        return redirect('/Api');
    }

    private function throttleKey(Request $request, string $email): string
    {
        return 'api-login:'.hash('sha256', mb_strtolower($email).'|'.ClientIpService::getClientIp($request));
    }
}
