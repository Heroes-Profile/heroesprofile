<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbilityLink extends Model
{
    protected $table = 'ability_links';

    protected $connection = 'heroesprofile';

    public $incrementing = false;

    public $timestamps = false;
}
