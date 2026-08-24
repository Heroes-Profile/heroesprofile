<?php

namespace Database\Seeders;

use App\Models\Api\ApiAccount;
use App\Models\Api\ApiKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * API portal accounts for a local database, one per state the API branches on.
 *
 * Keys are hashed and shown once, so a seeded account would otherwise be
 * unusable — nothing could recover a key to call with. These carry a fixed,
 * obviously-fake plaintext instead, printed at the end. That is only safe
 * because this never runs anywhere real: see the guards in run().
 */
class ApiAccountsSeeder extends Seeder
{
    private const CONNECTION = 'heroesprofile_api';

    private const PASSWORD = 'password';

    /**
     * [slug, name, plan id or null, comped flags, extra column overrides]
     *
     * `migrated` gates live data. An account without it gets fixtures, which is
     * what a new customer sees, so one account stays that way deliberately.
     */
    private const ACCOUNTS = [
        ['fixtures', 'Fixtures Account', null, [], ['migrated' => false]],
        ['basic', 'Basic Account', 1, [], ['migrated' => true]],
        ['developer', 'Developer Account', 3, [], ['migrated' => true, 'd_approved' => 1]],
        ['partner', 'Partner Account', null, ['p_approved'], ['migrated' => true]],
        ['admin', 'Admin Account', 3, [], ['migrated' => true, 'admin' => true, 'admin_mode' => true]],
    ];

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('ApiAccountsSeeder: not a local environment, skipping.');

            return;
        }

        $connection = DB::connection(self::CONNECTION);

        // A populated `users` means this is not a from-scratch local database.
        // Seeding fake accounts with known keys into one that holds real
        // customers would hand out working credentials.
        if ($connection->table('users')->exists()) {
            $this->command?->warn('ApiAccountsSeeder: API accounts already exist, skipping.');

            return;
        }

        $rows = [];

        foreach (self::ACCOUNTS as [$slug, $name, $planId, $comped, $overrides]) {
            $account = $this->account($slug, $name, $comped, $overrides);

            if ($planId !== null) {
                $this->subscribe($account, $slug, $planId);
            }

            $rows[] = [$account->email, self::PASSWORD, $this->key($account, $slug), $this->describe($planId, $comped)];
        }

        $this->command?->table(['Email', 'Password', 'API key', 'Resolves as'], $rows);
    }

    private function account(string $slug, string $name, array $comped, array $overrides): ApiAccount
    {
        $account = new ApiAccount;

        $account->forceFill(array_merge([
            'name' => $name,
            'email' => $slug.'@heroesprofile.test',
            'password' => Hash::make(self::PASSWORD),
            'terms_version_accepted' => config('api.terms_version'),
            'terms_accepted_at' => now(),
        ], array_fill_keys($comped, 1), $overrides));

        $account->save();

        return $account;
    }

    /**
     * A subscription Cashier and the key resolver both read. `stripe_price` is
     * what resolves to a plan, so it comes from the same config the billing page
     * uses rather than a literal.
     */
    private function subscribe(ApiAccount $account, string $slug, int $planId): void
    {
        $connection = DB::connection(self::CONNECTION);
        $price = config("api_plans.plans.{$planId}.stripe_price");
        $now = now();

        $connection->table('users')
            ->where('id', $account->id)
            ->update(['stripe_id' => 'cus_local_'.$slug, 'current_billing_plan' => $price]);

        $subscriptionId = $connection->table('cashier_subscriptions')->insertGetId([
            'user_id' => $account->id,
            'type' => ApiAccount::SUBSCRIPTION,
            'stripe_id' => 'sub_local_'.$slug,
            'stripe_status' => 'active',
            'stripe_price' => $price,
            'quantity' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $connection->table('cashier_subscription_items')->insert([
            'subscription_id' => $subscriptionId,
            'stripe_id' => 'si_local_'.$slug,
            'stripe_product' => 'prod_local_'.$slug,
            'stripe_price' => $price,
            'quantity' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** Fixed plaintext, padded to the length the portal issues. */
    private function key(ApiAccount $account, string $slug): string
    {
        $plain = str_pad($slug.'_local_api_key_', ApiKey::SECRET_LENGTH, '0');

        ApiKey::create([
            'api_account_id' => $account->id,
            'name' => 'Local',
            'secret_hash' => ApiKey::hash($plain),
        ]);

        return $plain;
    }

    private function describe(?int $planId, array $comped): string
    {
        $parts = [];

        if ($planId !== null) {
            $parts[] = config("api_plans.plans.{$planId}.name");
        }

        foreach ($comped as $flag) {
            $parts[] = config('api_plans.plans.'.config("api_plans.comped_flags.{$flag}").'.name').' (comped)';
        }

        return $parts === [] ? 'No plan — serves fixtures' : implode(' + ', $parts);
    }
}
