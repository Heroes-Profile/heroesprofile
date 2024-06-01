<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchPredictionPlayerStat extends Model
{
    protected $table = 'match_prediction_player_stats';

    protected $primaryKey = 'match_prediction_player_stats_id';

    public $timestamps = false;

    protected $fillable = ['battlenet_accounts_id', 'season', 'game_type', 'win', 'loss'];
}
