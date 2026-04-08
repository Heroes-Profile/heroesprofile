<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattletagNotAllowedDownloadReplay extends Model
{
    protected $table = 'battletag_not_allowed_download_replays';

    protected $primaryKey = 'battletag_not_allowed_download_replays_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
