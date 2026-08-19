<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiKey;
use App\Services\Api\ApiKeyResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiKeyController extends Controller
{
    private const MAX_ACTIVE_KEYS = 10;

    public function store(Request $request, ApiKeyResolver $resolver)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $account = Auth::guard('api_web')->user();

        $activeKeys = ApiKey::where('api_account_id', $account->id)->active()->count();

        if ($activeKeys >= self::MAX_ACTIVE_KEYS) {
            return response()->json([
                'error' => 'You already have the maximum of '.self::MAX_ACTIVE_KEYS.' active keys. Revoke one first.',
            ], 422);
        }

        [$key, $plainText] = ApiKey::generateFor($account, $validated['name']);

        // The only time the full key is ever available.
        return response()->json([
            'key' => $this->present($key) + ['plain_text' => $plainText],
        ]);
    }

    public function revoke(Request $request, ApiKeyResolver $resolver)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $account = Auth::guard('api_web')->user();

        $key = ApiKey::where('api_account_id', $account->id)
            ->where('id', $validated['id'])
            ->active()
            ->first();

        if (! $key) {
            return response()->json(['error' => 'Key not found.'], 404);
        }

        $hash = $key->secret_hash;
        $key->revoke();
        $resolver->forgetHash($hash);

        return response()->json(['revoked' => $key->id]);
    }

    public static function present(ApiKey $key): array
    {
        return [
            'id' => $key->id,
            'name' => $key->name,
            'created_at' => $key->created_at?->toDateString(),
            'last_used_at' => $key->last_used_at?->toDateTimeString(),
        ];
    }
}
