<?php

namespace App\Models\Esports\Other;

use Illuminate\Database\Eloquent\Model;

class OtherTeam extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'team_id';

    protected $connection = 'heroesprofile_esports_other';

    public $timestamps = false;
}
