<?php

namespace App\Support;

/**
 * The `hero_level` filter's bands.
 *
 * Hero levels are stored bucketed rather than exact, and the stored value is the
 * band's lower bound. So `hero_level=25` selects the 25-to-40 band — it is not a
 * minimum, and a value that is not one of these codes matches no rows at all
 * rather than returning everything above it.
 *
 * Mirrors the site's own filter dropdown
 * (`GlobalDataService::$filterData->hero_level`), which is where these came from.
 */
class HeroLevelBands
{
    /** @var array<string, string> */
    public const BANDS = [
        '1' => '1-5',
        '5' => '5-10',
        '10' => '10-15',
        '15' => '15-25',
        '25' => '25-40',
        '40' => '40-60',
        '60' => '60-80',
        '80' => '80-100',
        '100' => '100+',
    ];

    /** @return array<string, string> */
    public static function all(): array
    {
        return self::BANDS;
    }

    public static function isValid(?string $code): bool
    {
        return $code !== null && array_key_exists($code, self::BANDS);
    }
}
