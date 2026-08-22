<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Terms and privacy for the API section. Separate from the site's own pages —
 * subscriptions, refunds and the use license have no equivalent there.
 */
class LegalController extends Controller
{
    public function terms()
    {
        $account = Auth::guard('api_web')->user();
        $current = config('api.terms_version');

        return view('api.terms', [
            // Derived, not flashed: the banner has to survive a reload while the
            // account is still being held.
            'reviewRequired' => (bool) ($account && $current && $account->terms_version_accepted !== $current),
        ]);
    }

    public function privacy()
    {
        return view('api.privacy');
    }

    public function accept(Request $request)
    {
        $account = Auth::guard('api_web')->user();

        $account->terms_version_accepted = config('api.terms_version');
        $account->terms_accepted_at = now();
        $account->save();

        return redirect($request->session()->pull('terms_intended', '/Api/Account'));
    }
}
