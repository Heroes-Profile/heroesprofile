<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Composition extends Model
{
    protected $table = 'compositions';
    protected $primaryKey = 'composition_id';
    protected $connection = 'heroesprofile';

    public $timestamps = false;

    public function globalCompositions()
    {
        return $this->hasMany(GlobalCompositions::class, 'composition_id', 'composition_id');
    }
}
