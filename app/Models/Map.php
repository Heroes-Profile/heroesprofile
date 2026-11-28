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

    /** @return array{idsByName: array<string, int>, translations: array<string, string>, namesByShort: array<string, string>} */
    public static function parserLookups(): array
    {
        return [
            'idsByName' => static::pluck('map_id', 'name')->all(),
            'translations' => MapTranslation::englishNamesByLocalized(),
            'namesByShort' => static::pluck('name', 'short_name')->all(),
        ];
    }

    /**
     * Map id for the parser's map, by name, then translation, then short name, as the backend resolves it.
     *
     * @param  array<string, mixed>  $parsed
     * @param  array{idsByName: array<string, int>, translations: array<string, string>, namesByShort: array<string, string>}  $lookups
     */
    public static function idFromParser(array $parsed, array $lookups): ?int
    {
        $name = $parsed['map'] ?? '';

        if (array_key_exists($name, $lookups['idsByName'])) {
            return (int) $lookups['idsByName'][$name];
        }

        if (array_key_exists($name, $lookups['translations'])) {
            $translated = $lookups['translations'][$name];

            if (array_key_exists($translated, $lookups['idsByName'])) {
                return (int) $lookups['idsByName'][$translated];
            }
        }

        $short = $parsed['map_short'] ?? '';

        if (array_key_exists($short, $lookups['namesByShort'])) {
            $full = $lookups['namesByShort'][$short];

            if (array_key_exists($full, $lookups['idsByName'])) {
                return (int) $lookups['idsByName'][$full];
            }
        }

        return null;
    }

    public function getSanitizedMapNameAttribute()
    {
        $sanitize = str_replace(' ', '_', strtolower($this->name));
        $sanitize = str_replace("'", '', $sanitize);

        return $sanitize;
    }
}
