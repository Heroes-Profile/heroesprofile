<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per settled upload, used for the per-IP duplicate check.
 *
 * `fingerprint` and `replayID` hold what the attempt resolved to, so a resend of
 * the same bytes is answered from here rather than from the parser. Rows written
 * by the old API site leave both null.
 *
 * Lives in the API schema because the old API site owns it.
 */
class UploadAttempt extends Model
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'upload_attempts';

    public $timestamps = false;

    protected $fillable = ['ip', 'file_hash', 'file_name', 'file_size', 'status', 'fingerprint', 'replayID'];
}
