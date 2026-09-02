<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

/**
 * One enforcement action against an API account. Append-only; the only field ever
 * updated after the fact is `acknowledged_at` on a warning.
 */
class ApiAccountAction extends Model
{
    public const WARN = 'warn';

    public const SUSPEND = 'suspend';

    public const TERMINATE = 'terminate';

    public const REINSTATE = 'reinstate';

    /** Actions that withdraw access. Both set `users.suspended_at`. */
    public const ENFORCEMENT = [self::SUSPEND, self::TERMINATE];

    protected $connection = 'heroesprofile_api';

    protected $table = 'api_account_actions';

    protected $fillable = [
        'api_account_id',
        'action',
        'reason',
        'notes',
        'respond_by',
        'performed_by',
    ];

    protected $casts = [
        'respond_by' => 'date',
        'acknowledged_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(ApiAccount::class, 'api_account_id', 'id');
    }

    /** Warnings the customer has not dismissed yet. */
    public function scopeUnacknowledgedWarnings($query)
    {
        return $query->where('action', self::WARN)->whereNull('acknowledged_at');
    }

    public function acknowledge(): void
    {
        if ($this->acknowledged_at !== null) {
            return;
        }

        $this->forceFill(['acknowledged_at' => now()])->save();
    }

    /** Past its stated date and still unanswered. Nothing acts on this by itself. */
    public function isOverdue(): bool
    {
        return $this->action === self::WARN
            && $this->acknowledged_at === null
            && $this->respond_by !== null
            && now()->startOfDay()->gt($this->respond_by);
    }
}
