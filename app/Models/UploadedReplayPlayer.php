<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Storm League replay's player as the upload-time parse saw them, kept a few days to check the ranks the
 * uploader's rank reader sends - see RealRankService.
 */
class UploadedReplayPlayer extends Model
{
    protected $connection = 'heroesprofile';

    protected $table = 'uploaded_replay_players';

    public $timestamps = false;

    protected $fillable = ['replayID', 'battletag', 'region', 'team', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
