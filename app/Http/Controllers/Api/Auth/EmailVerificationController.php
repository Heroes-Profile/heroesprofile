<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * No auth middleware. The signature is the proof, and the link is routinely opened
     * in whatever browser the mail client hands it to — one that is usually not the one
     * they registered in.
     */
    public function verify(Request $request, string $id, string $hash)
    {
        $account = ApiAccount::find($id);

        if ($account === null || ! hash_equals($hash, sha1($account->getEmailForVerification()))) {
            return redirect('/Api/Login')->withErrors([
                'email' => 'That verification link is not valid.',
            ]);
        }

        if (! $account->hasVerifiedEmail()) {
            $account->markEmailAsVerified();

            event(new Verified($account));
        }

        return redirect('/Api/Login')->with('status', 'Your email address is verified.');
    }
}
