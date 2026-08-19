<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $account = Auth::guard('api_web')->user();

        // Trimmed rather than the whole model — `users` also holds billing address,
        // stripe ids and entitlement flags that have no business in the page source.
        return view('api.account.index', [
            'account' => [
                'name' => $account->name,
                'email' => $account->email,
                'migrated' => $account->hasMigrated(),
            ],
        ]);
    }
}
