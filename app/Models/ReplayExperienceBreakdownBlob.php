<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplayExperienceBreakdownBlob extends Model
{
    protected $table = 'replay_experience_breakdown_blob';
    protected $primaryKey = 'replay_experience_breakdown_id';
    protected $connection = 'heroesprofile';

    public $timestamps = false;

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
