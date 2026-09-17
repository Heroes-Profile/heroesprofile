<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Full session swap, so pages render exactly as the customer sees them — including
 * whatever is breaking for them.
 *
 * The consequence is that every action taken while impersonating is genuinely theirs:
 * a cancellation cancels their subscription, a new key is a key on their account.
 * Hence the banner on every page, and hence `stop()` being reachable without the admin
 * grant — once swapped, the session is no longer an admin's.
 */
class ImpersonationController extends Controller
{
    /** Session key holding the real admin's id while a swap is in effect. */
    public const SESSION_KEY = 'api_impersonator';

    /** Session key holding the impersonated account's id, so Stop only works from inside the swap. */
    public const TARGET_SESSION_KEY = 'api_impersonated';

    /** Ends any swap marker. Called on stop, logout and login so a stale one can't be reused. */
    public static function forget(Request $request): void
    {
        $request->session()->forget([self::SESSION_KEY, self::TARGET_SESSION_KEY]);
    }

    public function start(Request $request, int $id)
    {
        $admin = Auth::guard('api_web')->user();

        // Nesting would leave no way back to the original admin: the key holds one id.
        if ($request->session()->has(self::SESSION_KEY)) {
            return response()->json(['error' => 'Already impersonating. Stop first.'], 409);
        }

        $target = ApiAccount::find($id);

        if ($target === null) {
            return response()->json(['error' => 'No such account.'], 404);
        }

        if ($target->id === $admin->id) {
            return response()->json(['error' => 'That is your own account.'], 422);
        }

        // An admin session would keep the Admin nav and the console reachable, which
        // is how a swap ends up nested by accident.
        if ($target->isAdmin()) {
            return response()->json(['error' => 'Administrators cannot be impersonated.'], 422);
        }

        Auth::guard('api_web')->login($target);

        // After the login, not before: the guard migrates the session as it logs in.
        $request->session()->put(self::SESSION_KEY, $admin->id);
        $request->session()->put(self::TARGET_SESSION_KEY, $target->id);

        return response()->json(['ok' => true, 'redirect' => '/Api/Account']);
    }

    /**
     * Deliberately not behind `ensureApiAdmin` — the session belongs to the customer
     * at this point, so that guard would refuse and strand the admin.
     */
    public function stop(Request $request)
    {
        $adminId = $request->session()->get(self::SESSION_KEY);
        $targetId = $request->session()->get(self::TARGET_SESSION_KEY);
        $current = Auth::guard('api_web')->user();

        // The swap is only undone from inside it: the signed-in account must be the one
        // that was impersonated. Anything else is a stale marker, which is discarded.
        if ($adminId === null || $targetId === null || $current === null || (int) $current->id !== (int) $targetId) {
            self::forget($request);

            return redirect('/Api/Account');
        }

        $admin = ApiAccount::find($adminId);

        // Re-checked rather than trusted: the grant could have been withdrawn, or the
        // account deleted, while the swap was in effect.
        if ($admin === null || ! $admin->isAdmin()) {
            Auth::guard('api_web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/Api/Login');
        }

        Auth::guard('api_web')->login($admin);

        self::forget($request);

        return redirect('/Api/Admin');
    }
}
