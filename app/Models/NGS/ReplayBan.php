<?php

namespace App\Models\NGS;

use Illuminate\Database\Eloquent\Model;

class ReplayBan extends Model
{
    protected $table = 'replay_bans';
    protected $primaryKey = 'ban_id';
    protected $connection = 'heroesprofile_ngs';

    public $timestamps = false;
}
