<?php

namespace App\Models\CCL;

use Illuminate\Database\Eloquent\Model;

class CCLTeam extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'team_id';

    protected $connection = 'heroesprofile_ccl';

    public $timestamps = false;
}
