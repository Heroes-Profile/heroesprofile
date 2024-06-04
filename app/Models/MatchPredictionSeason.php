<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchPredictionSeason extends Model
{
    protected $table = 'match_prediction_season';

    protected $primaryKey = 'match_prediction_season_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
