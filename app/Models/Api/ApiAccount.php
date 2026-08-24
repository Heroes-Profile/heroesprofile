<?php

namespace App\Models\Api;

use App\Notifications\Api\ResetApiPassword;
use App\Notifications\Api\VerifyApiEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

/**
 * API customer account. Shares `heroesprofile_api.users` with the old API site.
 */
class ApiAccount extends Authenticatable implements MustVerifyEmail
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
        'admin' => 'boolean',
        'admin_mode' => 'boolean',
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
     * Nothing is gated on verification — no route carries the `verified` middleware.
     * Existing accounts predate this and hold a null `email_verified_at`, so enforcing
     * it would lock out paying customers mid-transition.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyApiEmail);
    }

    /** Scoped to this model so the main site's `web` guard keeps Laravel's default. */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetApiPassword($token));
    }

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
     *
     * An admin exercising their grant is judged on `test_mode` alone. Migration
     * is about not pulling live data from two sites at once, which does not apply
     * to the person running the site — and without this an admin could never see
     * live data to check it against.
     */
    public function receivesTestData(): bool
    {
        if ($this->actingAsAdmin()) {
            return $this->inTestMode();
        }

        return ! $this->hasMigrated() || $this->inTestMode();
    }

    /** Whether this account has been granted admin. Set in the database only. */
    public function isAdmin(): bool
    {
        return (bool) $this->admin;
    }

    /**
     * Whether the grant is currently being exercised.
     *
     * An admin can switch this off to be treated as an ordinary account — same
     * quota, same key requirement, same migration gate — which is the only way to
     * see what a customer sees without giving up the grant.
     */
    public function actingAsAdmin(): bool
    {
        return $this->isAdmin() && (bool) $this->admin_mode;
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
