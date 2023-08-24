<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TalentCombination extends Model
{
    protected $table = 'talent_combinations';
    protected $primaryKey = 'talent_combination_id';
    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
