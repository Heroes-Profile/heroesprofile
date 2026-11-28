<?php

namespace App\Services;

use App\Models\Hero;
use App\Models\HeroTranslation;
use App\Models\Map;
use App\Models\MapTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Hero, map, talent and ability text in the visitor's language, from heroesprofile_translations.
 * English names stay the keys everywhere; these are only added on top for display.
 */
class LocalizedGameStrings
{
    private const CACHE_SECONDS = 3600;

    private const FIELDS = ['name', 'short_description', 'description', 'raw_description'];

    /** @return array{heroes: array<string, string>, maps: array<string, string>} English name => localized name */
    public function names(string $locale): array
    {
        if ($locale === 'en') {
            return ['heroes' => [], 'maps' => []];
        }

        return $this->remember('names|'.$locale, function () use ($locale) {
            $heroes = Hero::pluck('name', 'id');
            $maps = Map::pluck('name', 'map_id');

            return [
                'heroes' => HeroTranslation::where('locale', $locale)->pluck('name', 'hero_id')
                    ->mapWithKeys(fn ($name, $id) => isset($heroes[$id]) ? [$heroes[$id] => $name] : [])
                    ->all(),
                'maps' => MapTranslation::where('locale', $locale)->pluck('name', 'map_id')
                    ->mapWithKeys(fn ($name, $id) => isset($maps[$id]) ? [$maps[$id] => $name] : [])
                    ->all(),
            ];
        });
    }

    /** @return array<string, string|null> the hero's localized name, title, description and lore; empty for English */
    public function hero(string $locale, int $heroId): array
    {
        if ($locale === 'en') {
            return [];
        }

        return $this->remember('hero|'.$locale.'|'.$heroId, fn () => (array) (HeroTranslation::where('locale', $locale)
            ->where('hero_id', $heroId)
            ->first(['name', 'title', 'description', 'lore'])
            ?->toArray() ?? []));
    }

    /** @return array<int, array<string, string|null>> talent_id => localized fields */
    public function talents(string $locale): array
    {
        if ($locale === 'en') {
            return [];
        }

        return $this->remember('talents|'.$locale, fn () => DB::connection('heroesprofile_translations')
            ->table('talents')
            ->where('locale', $locale)
            ->get(['talent_id', ...self::FIELDS])
            ->mapWithKeys(fn ($row) => [$row->talent_id => $this->fields($row)])
            ->all());
    }

    /** @return array<string, array<string, string|null>> ability link_id => localized fields */
    public function abilities(string $locale, string $heroName): array
    {
        if ($locale === 'en') {
            return [];
        }

        return $this->remember('abilities|'.$locale.'|'.$heroName, fn () => DB::connection('heroesprofile_translations')
            ->table('abilities')
            ->where('locale', $locale)
            ->where('hero_name', $heroName)
            ->get(['link_id', ...self::FIELDS])
            ->mapWithKeys(fn ($row) => [$row->link_id => $this->fields($row)])
            ->all());
    }

    /**
     * Adds localized_* fields to every talent row (talent_id + title) anywhere in $data.
     * Rows without a translation are left as they are, so the English text shows.
     */
    public function localizeTalents(array $data, string $locale): array
    {
        $talents = $this->talents($locale);

        if ($talents !== []) {
            $this->walk($data, $talents);
        }

        return $data;
    }

    /** @param array<int, array<string, mixed>> $abilities */
    public function localizeAbilities(array $abilities, string $locale, string $heroName): array
    {
        $strings = $this->abilities($locale, $heroName);

        foreach ($abilities as &$ability) {
            if (isset($ability['link_id'], $strings[$ability['link_id']])) {
                $ability += $strings[$ability['link_id']];
            }
        }

        return $abilities;
    }

    private function walk(array &$node, array $talents): void
    {
        if (isset($node['talent_id'], $node['title']) && isset($talents[(int) $node['talent_id']])) {
            $node += $talents[(int) $node['talent_id']];
        }

        foreach ($node as &$child) {
            if (is_array($child)) {
                $this->walk($child, $talents);
            }
        }
    }

    /**
     * Cached for an hour, but only once there's something to cache: a lookup made before the
     * translation tables were filled would otherwise hide them for the full hour.
     */
    private function remember(string $key, \Closure $build): array
    {
        $key = 'game_strings_v2|'.$key;
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $value = $build();
        if ($value !== [] && $value !== ['heroes' => [], 'maps' => []]) {
            Cache::put($key, $value, self::CACHE_SECONDS);
        }

        return $value;
    }

    /** @return array<string, string|null> */
    private function fields(object $row): array
    {
        $fields = [];

        foreach (self::FIELDS as $field) {
            $fields['localized_'.$field] = $row->{$field};
        }

        return $fields;
    }
}
