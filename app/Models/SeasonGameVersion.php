<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonGameVersion extends Model
{
    protected $table = 'season_game_versions';

    protected $primaryKey = 'id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    protected $fillable = [
        'season',
        'game_version',
        'date_added',
        'valid_globals',
        'patch_notes_url',
    ];

    protected $casts = [
        'season' => 'integer',
        'major' => 'integer',
        'minor' => 'integer',
        'patch' => 'integer',
        'build' => 'integer',
        'date_added' => 'datetime',
        'valid_globals' => 'boolean',
    ];
}
