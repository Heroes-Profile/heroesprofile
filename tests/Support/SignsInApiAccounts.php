<?php

namespace Tests\Support;

use App\Models\Api\ApiAccount;

/** The seeded API portal accounts (ApiAccountsSeeder), signed in on the portal's own guard. */
trait SignsInApiAccounts
{
    /** fixtures, basic, developer, partner or admin */
    protected function apiAccount(string $slug): ApiAccount
    {
        return ApiAccount::where('email', $slug.'@heroesprofile.test')->firstOrFail();
    }

    /** Signed in to the API portal only; the site's own (Battle.net) guard stays the default, as in a real request. */
    protected function asApiAccount(ApiAccount|string $account): static
    {
        $account = is_string($account) ? $this->apiAccount($account) : $account;

        $this->keepsSignedInUser = true;
        $this->app['auth']->guard('api_web')->setUser($account);

        return $this;
    }
}
