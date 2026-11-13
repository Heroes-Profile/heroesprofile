<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroTranslation extends Model
{
    protected $table = 'heroes';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $connection = 'heroesprofile_translations';

    public $timestamps = false;

    /** @return array<string, string> localized name (as-is and lowercased) => English hero name */
    public static function englishNamesByLocalized(): array
    {
        $english = Hero::pluck('name', 'id');
        $names = [];

        foreach (static::get(['hero_id', 'name']) as $row) {
            $name = $english[$row->hero_id] ?? null;

            if ($name !== null) {
                $names[$row->name] ??= $name;
                $names[mb_strtolower($row->name)] ??= $name;
            }
        }

        return $names;
    }

    /** Case- and accent-insensitive, through the column collation. */
    public static function heroIdFor(string $localized): ?int
    {
        $id = static::where('name', $localized)->value('hero_id');

        return $id === null ? null : (int) $id;
    }
}
