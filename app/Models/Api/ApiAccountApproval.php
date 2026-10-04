<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

/**
 * An approval, or an approval flag granted or removed, with the project
 * description at the time. Append-only apart from `notes`.
 */
class ApiAccountApproval extends Model
{
    /** The Approve button. Records the decision; access is unchanged. */
    public const APPROVAL = 'approval';

    /** A Comped Access checkbox changed. */
    public const FLAG = 'flag';

    protected $connection = 'heroesprofile_api';

    protected $table = 'api_account_approvals';

    protected $fillable = [
        'api_account_id',
        'type',
        'flag',
        'granted',
        'project_name',
        'project_description',
        'project_updated_at',
        'notes',
        'performed_by',
    ];

    protected $casts = [
        'granted' => 'boolean',
        'project_updated_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(ApiAccount::class, 'api_account_id', 'id');
    }
}
