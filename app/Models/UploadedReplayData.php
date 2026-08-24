<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Where a replay came from: which uploader, which version, which machine. */
class UploadedReplayData extends Model
{
    protected $connection = 'heroesprofile';

    protected $table = 'uploaded_replay_data';

    protected $primaryKey = 'uploaded_replay_data_id';

    protected $fillable = [
        'replayID', 'uploaded_filename', 'uploaded_source', 'uploader_version',
        'uploader_compile_checker', 'uploader_start_diff', 'game_date',
        'upload_team', 'ip',
    ];

    protected $casts = [
        'game_date' => 'datetime',
        'uploader_start_diff' => 'datetime',
    ];
}
