<?php

namespace App\Models\MastersClash;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MastersClashTeam extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'team_id';

    protected $connection = 'heroesprofile_mcl';

    public $timestamps = false;
}
