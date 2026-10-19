<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One period of a player's real Storm League rank, as the uploader's rank reader read it off the loading
 * screen - see RealRankService.
 */
class BattletagRealRank extends Model
{
    protected $connection = 'heroesprofile';

    protected $table = 'battletags_real_ranks';

    public $timestamps = false;

    protected $fillable = [
        'player_id', 'blizz_id', 'region', 'season',
        'league_tier', 'division', 'grandmaster', 'ladder_position', 'unranked',
        'first_replayID', 'first_game_date', 'last_replayID', 'last_game_date', 'read_count',
        'reporter', 'uploader_version',
    ];

    protected $casts = [
        'player_id' => 'integer',
        'blizz_id' => 'integer',
        'region' => 'integer',
        'season' => 'integer',
        'league_tier' => 'integer',
        'division' => 'integer',
        'ladder_position' => 'integer',
        'first_replayID' => 'integer',
        'last_replayID' => 'integer',
        'read_count' => 'integer',
        'grandmaster' => 'boolean',
        'unranked' => 'boolean',
        'first_game_date' => 'datetime',
        'last_game_date' => 'datetime',
    ];

    public function battletag()
    {
        return $this->belongsTo(Battletag::class, 'player_id', 'player_id');
    }
}
