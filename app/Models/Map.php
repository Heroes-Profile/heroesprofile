<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $table = 'maps';

    protected $primaryKey = 'map_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    protected $appends = ['sanitized_map_name'];

    public function getSanitizedMapNameAttribute()
    {
        $sanitize = str_replace(' ', '_', strtolower($this->name));
        $sanitize = str_replace("'", '', $sanitize);

        return $sanitize;
    }
}
