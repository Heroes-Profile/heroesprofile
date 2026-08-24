<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Every upload attempt including rejections, with the reason. */
class UploadAttemptLog extends Model
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'upload_attempt_logs';

    public $timestamps = false;

    protected $fillable = ['ip', 'file_hash', 'file_name', 'file_size', 'source', 'status', 'message'];
}
