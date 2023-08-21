<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $table = 'maps'; // Set the table name if it's different
    protected $primaryKey = 'map_id';
    public $timestamps = false;
    protected $connection = 'heroesprofile';
}
