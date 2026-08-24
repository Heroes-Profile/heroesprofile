<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class ApiEndpointQuota extends Model
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'api_endpoint_quotas';

    public $incrementing = false;

    protected $fillable = [
        'endpoint_id',
        'subscription_plan',
        'calls_per_week',
    ];

    protected $casts = [
        'calls_per_week' => 'integer',
    ];

    public function endpoint()
    {
        return $this->belongsTo(ApiEndpoint::class, 'endpoint_id', 'endpoint_id');
    }
}
