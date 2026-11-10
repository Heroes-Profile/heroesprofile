<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

/** A marked value sent to a poisoned account, and what the site had then. See PoisonPill. */
class ApiPoisonLog extends Model
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'api_poison_log';

    public $timestamps = false;

    protected $casts = [
        'params' => 'array',
        'real_value' => 'float',
        'sent_value' => 'float',
        'first_sent_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(ApiAccount::class, 'api_account_id', 'id');
    }

    public function present(): array
    {
        return [
            'id' => $this->id,
            'endpoint' => $this->endpoint,
            'params' => $this->params,
            'label' => $this->label,
            'identity' => $this->identity,
            'real_value' => $this->real_value,
            'sent_value' => $this->sent_value,
            'times_sent' => $this->times_sent,
            'first_sent_at' => $this->first_sent_at?->toDateTimeString(),
            'last_sent_at' => $this->last_sent_at?->toDateTimeString(),
        ];
    }
}
