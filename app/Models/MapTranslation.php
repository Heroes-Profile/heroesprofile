<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapTranslation extends Model
{
    protected $table = 'maps';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $connection = 'heroesprofile_translations';

    public $timestamps = false;

    /** @return array<string, string> localized name (as-is and lowercased) => English map name */
    public static function englishNamesByLocalized(): array
    {
        $english = Map::pluck('name', 'map_id');
        $names = [];

        foreach (static::get(['map_id', 'name']) as $row) {
            $name = $english[$row->map_id] ?? null;

            if ($name !== null) {
                $names[$row->name] ??= $name;
                $names[mb_strtolower($row->name)] ??= $name;
            }
        }

        return $names;
    }
}
