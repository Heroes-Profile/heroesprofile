<?php

namespace App\Models\Api;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

/**
 * API customer account. Shares `heroesprofile_api.users` with the old API site.
 */
class ApiAccount extends Authenticatable
{
    use Billable, Notifiable;

    protected $connection = 'heroesprofile_api';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'authy_id',
        'two_factor_reset_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'last_read_announcements_at' => 'datetime',
        'migrated' => 'boolean',
        'test_mode' => 'boolean',
        'uses_two_factor_auth' => 'boolean',
    ];

    /** Cashier subscription name. The old site used 'default' too. */
    public const SUBSCRIPTION = 'default';

    /** Comped access flags, granted by hand per partner or esports org. */
    public const APPROVAL_COLUMNS = [
        'd_approved',
        'p_approved',
        'n_approved',
        'n_upload_approved',
        'h_approved',
        'c_upload_approved',
        'hi_upload_approved',
        'nut_upload_approved',
        'm_upload_approved',
        'ml_upload_approved',
    ];

    /**
     * Cashier derives its foreign key from the class name, which would give
     * `api_account_id`. The table is `users`, so the column is `user_id`.
     */
    public function getForeignKey()
    {
        return 'user_id';
    }

    /** Unmigrated accounts get fixtures from the public API, not live data. */
    public function hasMigrated(): bool
    {
        return (bool) $this->migrated;
    }

    /** Opt-in fixtures, switchable at any time once migrated. */
    public function inTestMode(): bool
    {
        return (bool) $this->test_mode;
    }

    /**
     * Fixtures instead of live data, and no quota consumed. True while an account
     * has not migrated, or whenever it has test mode switched on.
     */
    public function receivesTestData(): bool
    {
        return ! $this->hasMigrated() || $this->inTestMode();
    }

    /**
     * NGS data is a granted permission rather than a purchased tier, so it is not
     * sold, not priced and not visible in the plan tables. Either flag is enough
     * to read; writing needs both, as the old API required.
     */
    public function hasNgsAccess(): bool
    {
        return (bool) $this->n_approved || (bool) $this->n_upload_approved;
    }

    public function hasNgsUploadAccess(): bool
    {
        return (bool) $this->n_approved && (bool) $this->n_upload_approved;
    }

    public function hasComptedAccess(): bool
    {
        foreach (self::APPROVAL_COLUMNS as $column) {
            if ((bool) $this->getAttribute($column)) {
                return true;
            }
        }

        return false;
    }
}
