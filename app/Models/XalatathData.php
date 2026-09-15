<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XalatathData extends Model
{
    protected $table = 'xalatath_data';

    protected $primaryKey = 'game_type';

    protected $connection = 'heroesprofile';

    public $incrementing = false;

    public $timestamps = false;
}
