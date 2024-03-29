<?php

namespace App\Models\NGS;

use Illuminate\Database\Eloquent\Model;

class NGSTeam extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'team_id';

    protected $connection = 'heroesprofile_ngs';

    public $timestamps = false;
}
