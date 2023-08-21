<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonGameVersion extends Model
{
    protected $table = 'season_game_versions';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $connection = 'heroesprofile';
}