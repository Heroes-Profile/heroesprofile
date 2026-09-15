<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class XalatathEventController extends Controller
{
    public function totals()
    {
        $event = $this->globalDataService->getXalatathEvent();

        if (! $event) {
            return response()->noContent();
        }

        return response()->json($event);
    }

    // Flair the frontend resolves itself (so cached page data doesn't need it).
    public function flairState()
    {
        return response()->json([
            'void_eye' => $this->globalDataService->getVoidEyeHolders(),
            'owner_hidden' => $this->globalDataService->isFlairHidden('owner', 67280, 1),
        ]);
    }

    public function claimEye(Request $request)
    {
        if (! $this->globalDataService->getXalatathEvent()) {
            return response()->json(['status' => 'closed'], 410);
        }

        try {
            $token = json_decode(Crypt::decryptString((string) $request->input('token')), true);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'invalid'], 422);
        }

        $visitor = $this->globalDataService->voidEyeVisitorKey($request);

        if (! is_array($token) || ($token['expires'] ?? 0) < now()->timestamp || ($token['visitor'] ?? null) !== $visitor) {
            return response()->json(['status' => 'invalid'], 422);
        }

        if (Auth::check()) {
            $this->globalDataService->awardVoidEye(Auth::user());

            return response()->json(['status' => 'claimed']);
        }

        // Awarded in the Battle.net login callback.
        $request->session()->put('void_eye_pending', true);

        return response()->json(['status' => 'login']);
    }
}
