<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class ApiEndpoint extends Model
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'api_endpoints';

    protected $primaryKey = 'endpoint_id';

    protected $fillable = [
        'endpoint',
        'name',
        'group_name',
        'group_sort',
        'sort',
    ];

    public function quotas()
    {
        return $this->hasMany(ApiEndpointQuota::class, 'endpoint_id', 'endpoint_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('group_sort')->orderBy('sort');
    }

    /** Esports groups are comped and not part of the public pricing tiers. */
    public function scopeExcludingEsports($query)
    {
        return $query->where('group_name', 'NOT LIKE', '%NGS%');
    }
}
