<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiKey;
use App\Services\Api\UsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ],
            'keys' => $keys,
            'usage' => $usage->forAccount($account),
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
}
