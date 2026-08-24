<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Recorded when a replay first seen from another source is later re-uploaded by
 * the desktop or electron client. Table name is singular in production.
 */
class UploaderSourceChange extends Model
{
    protected $connection = 'heroesprofile';

    protected $table = 'uploader_source_change';

    protected $primaryKey = 'uploader_source_change_id';

    protected $fillable = ['replayID', 'source', 'parsed'];
}
