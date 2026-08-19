<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Cashier;
use Throwable;

/**
 * Copies the existing `subscriptions` table into `cashier_subscriptions`.
 *
 * Stripe customer and subscription ids are carried across unchanged, so nothing
 * changes on Stripe's side — this only gives Cashier its own view of subscriptions
 * that already exist.
 *
 * Reports only unless --write is passed. Idempotent: rows already present by
 * stripe_id are skipped, so it is safe to run repeatedly.
 */
class BackfillCashierSubscriptions extends Command
{
    protected $signature = 'api:backfill-subscriptions
                            {--write : Actually write. Without this the command only reports.}
                            {--skip-stripe : Do not reconcile against the Stripe API.}';

    protected $description = 'Copy existing subscriptions into Cashier tables, reconciling against Stripe';

    private const CONNECTION = 'heroesprofile_api';

    public function handle(): int
    {
        $write = (bool) $this->option('write');
        $checkStripe = ! $this->option('skip-stripe') && filled(config('cashier.secret'));

        if (! $this->option('skip-stripe') && ! $checkStripe) {
            $this->warn('No Stripe secret configured — skipping reconciliation.');
        }

        $this->line($write ? '<fg=yellow>WRITING</> to cashier_subscriptions.' : 'Dry run. Nothing will be written.');
        $this->newLine();

        $rows = DB::connection(self::CONNECTION)->table('subscriptions')->orderBy('id')->get();

        $counts = ['copy' => 0, 'skip' => 0, 'mismatch' => 0, 'missing' => 0, 'error' => 0];
        $problems = [];

        foreach ($rows as $row) {
            $exists = DB::connection(self::CONNECTION)
                ->table('cashier_subscriptions')
                ->where('stripe_id', $row->stripe_id)
                ->exists();

            if ($exists) {
                $counts['skip']++;

                continue;
            }

            $remoteStatus = null;

            if ($checkStripe) {
                [$problem, $remoteStatus] = $this->reconcile($row);

                if ($problem !== null) {
                    $counts[$problem['type']]++;
                    $problems[] = $problem;

                    // Not copied on purpose. These are hand-created comped grants
                    // sharing one placeholder customer id, which the UNIQUE index
                    // on stripe_id would reject anyway. Comped access now resolves
                    // from the approval flags instead (config/api_plans.php).
                    if ($problem['type'] === 'missing') {
                        continue;
                    }
                }
            }

            $status = $this->resolveStatus($row, $remoteStatus);

            if ($status === null) {
                $counts['error']++;
                $problems[] = [
                    'type' => 'error',
                    'stripe_id' => $row->stripe_id,
                    'local' => 'null status',
                    'stripe' => 'unknown — re-run without --skip-stripe',
                ];

                continue;
            }

            if ($write) {
                DB::connection(self::CONNECTION)->table('cashier_subscriptions')->insert([
                    'id' => $row->id,
                    'user_id' => $row->user_id,
                    'type' => $row->name,
                    'stripe_id' => $row->stripe_id,
                    'stripe_status' => $status,
                    'stripe_price' => $row->stripe_plan,
                    'quantity' => $row->quantity,
                    'trial_ends_at' => $row->trial_ends_at,
                    'ends_at' => $row->ends_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            $counts['copy']++;
        }

        $cards = $this->backfillPaymentMethodColumns($write);

        $this->table(
            ['Result', 'Count'],
            [
                ['To copy', $counts['copy']],
                ['Already present (skipped)', $counts['skip']],
                ['Stripe status/price mismatch', $counts['mismatch']],
                ['Not found in Stripe (not copied)', $counts['missing']],
                ['Errors (unresolvable status / lookup failed)', $counts['error']],
                ['Payment method columns to fill', $cards],
            ]
        );

        if ($problems !== []) {
            $this->newLine();
            $this->warn('Reconciliation problems:');
            $this->table(
                ['stripe_id', 'Type', 'Local', 'Stripe'],
                array_map(fn ($p) => [$p['stripe_id'], $p['type'], $p['local'], $p['stripe']], $problems)
            );
        }

        if (! $write) {
            $this->newLine();
            $this->info('Re-run with --write once the numbers above look right.');
        }

        return $counts['error'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * `cashier_subscriptions.stripe_status` is NOT NULL, but a handful of legacy
     * rows have no status. Stripe is authoritative where it knows.
     */
    private function resolveStatus(object $row, ?string $remoteStatus): ?string
    {
        return $row->stripe_status ?: $remoteStatus;
    }

    /**
     * @return array{0: array{type: string, stripe_id: string, local: string, stripe: string}|null, 1: ?string}
     */
    private function reconcile(object $row): array
    {
        try {
            $remote = Cashier::stripe()->subscriptions->retrieve($row->stripe_id);
        } catch (Throwable $e) {
            $notFound = str_contains($e->getMessage(), 'No such subscription');

            return [[
                'type' => $notFound ? 'missing' : 'error',
                'stripe_id' => $row->stripe_id,
                'local' => (string) $row->stripe_status,
                'stripe' => $notFound ? 'not found' : $e->getMessage(),
            ], null];
        }

        $remotePrice = $remote->items->data[0]->price->id ?? null;

        if ($remote->status !== $row->stripe_status || $remotePrice !== $row->stripe_plan) {
            return [[
                'type' => 'mismatch',
                'stripe_id' => $row->stripe_id,
                'local' => ($row->stripe_status ?: 'null').' / '.$row->stripe_plan,
                'stripe' => $remote->status.' / '.($remotePrice ?? 'none'),
            ], $remote->status];
        }

        return [null, $remote->status];
    }

    /** The existing card_* columns are what Cashier 15 reads as pm_*. */
    private function backfillPaymentMethodColumns(bool $write): int
    {
        $query = DB::connection(self::CONNECTION)
            ->table('users')
            ->whereNull('pm_type')
            ->whereNotNull('card_brand');

        $count = $query->count();

        if ($write && $count > 0) {
            $query->update([
                'pm_type' => DB::raw('`card_brand`'),
                'pm_last_four' => DB::raw('`card_last_four`'),
            ]);
        }

        return $count;
    }
}
