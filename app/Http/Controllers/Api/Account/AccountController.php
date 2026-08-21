<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use App\Models\Api\ApiKey;
use App\Services\Api\UsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(UsageService $usage)
    {
        $account = Auth::guard('api_web')->user();

        $keys = ApiKey::where('api_account_id', $account->id)
            ->active()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ApiKey $key) => ApiKeyController::present($key))
            ->all();

        // Trimmed rather than the whole model — `users` also holds billing address,
        // stripe ids and entitlement flags that have no business in the page source.
        return view('api.account.index', [
            'account' => [
                'name' => $account->name,
                'email' => $account->email,
                'migrated' => $account->hasMigrated(),
                'test_mode' => $account->inTestMode(),
                'receives_test_data' => $account->receivesTestData(),
                'has_legacy_token' => $this->hasLegacyToken($account),
            ],
            'keys' => $keys,
            'usage' => $usage->forAccount($account),
        ]);
    }

    /**
     * Activates live data. One way: it also expires every old-site token the
     * account holds, which is what stops them pulling live data from both sites
     * at once. Spark's TokenGuard already honours `expires_at`, so the old site
     * stops accepting their key with no code change there.
     */
    public function migrate()
    {
        $account = Auth::guard('api_web')->user();

        if ($account->hasMigrated()) {
            return response()->json(['error' => 'This account already uses live data.'], 409);
        }

        // Activating expires their old key, so they would otherwise be left with
        // no working key at all.
        $hasKey = ApiKey::where('api_account_id', $account->id)->active()->exists();

        if (! $hasKey) {
            return response()->json([
                'error' => 'Create an API key first. Activating live data expires your old key immediately.',
            ], 422);
        }

        DB::connection('heroesprofile_api')->transaction(function () use ($account) {
            $account->forceFill(['migrated' => true])->save();

            DB::connection('heroesprofile_api')
                ->table('api_tokens')
                ->where('user_id', $account->id)
                ->update(['expires_at' => now()]);
        });

        return response()->json([
            'migrated' => $account->hasMigrated(),
            'receives_test_data' => $account->receivesTestData(),
        ]);
    }

    public function setTestMode(Request $request)
    {
        $validated = $request->validate([
            'test_mode' => ['required', 'boolean'],
        ]);

        $account = Auth::guard('api_web')->user();
        $account->forceFill(['test_mode' => $validated['test_mode']])->save();

        return response()->json([
            'test_mode' => $account->inTestMode(),
            'receives_test_data' => $account->receivesTestData(),
        ]);
    }

    /**
     * Whether they hold an old-site key. Accounts registered here have nothing to
     * migrate, so their control reads "activate" rather than "migrate".
     */
    private function hasLegacyToken(ApiAccount $account): bool
    {
        return DB::connection('heroesprofile_api')
            ->table('api_tokens')
            ->where('user_id', $account->id)
            ->exists();
    }
}
