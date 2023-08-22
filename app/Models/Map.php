<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $table = 'maps'; 
    protected $primaryKey = 'map_id';
    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
