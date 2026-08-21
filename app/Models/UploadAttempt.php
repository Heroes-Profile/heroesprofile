<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per accepted or rejected upload, used for the per-IP duplicate check.
 * Lives in the API schema because the old API site owns it.
 */
class UploadAttempt extends Model
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'upload_attempts';

    public $timestamps = false;

    protected $fillable = ['ip', 'file_hash', 'file_name', 'file_size', 'status'];
}
