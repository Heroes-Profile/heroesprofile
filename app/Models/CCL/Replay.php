<?php

namespace App\Models\CCL;

use Illuminate\Database\Eloquent\Model;

class CCLReplay extends Model
{
    protected $table = 'replay';

    protected $primaryKey = 'replayID';

    protected $connection = 'heroesprofile_ccl';

    public $timestamps = false;

    public function players()
    {
        return $this->hasMany(Player::class, 'replayID', 'replayID');
    }
}
