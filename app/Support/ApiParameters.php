<?php

namespace App\Support;

use App\Models\GameType;
use Illuminate\Support\Facades\Cache;

/**
 * Translates the names the API takes into the codes its internals want.
 *
 * The public contract is names everywhere — `ARAM` not `ar`, `NA` not `1` — the
 * same rule `hero` already follows. The internal controllers were written for the
 * site's own filter dropdowns and take whatever those happened to send, which is
 * short codes in one place and numeric ids in another, and disagrees between the
 * global and player controllers for the same parameter.
 *
 * Callers may still send the internal form; both are accepted, so nothing that
 * worked against the old API stops working.
 */
class ApiParameters
{
    /**
     * The game types the API offers. Anything else in `game_types` is not sold or
     * not current — Brawl in particular has no data behind it.
     */
    public const GAME_TYPES = ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'];

    private const REGIONS = [1 => 'NA', 2 => 'EU', 3 => 'KR', 5 => 'CN'];

    private const CACHE_SECONDS = 86400;

    /**
     * Short codes for one or more game types, given codes or display names.
     *
     * @return array{0: array<int, string>, 1: array<int, string>} [codes, unknown]
     */
    public static function gameTypes(string|array $input): array
    {
        $lookup = self::gameTypeLookup();
        $codes = [];
        $unknown = [];

        foreach (self::split($input) as $value) {
            $key = mb_strtolower(trim($value));

            if (isset($lookup[$key])) {
                $codes[] = $lookup[$key];
            } else {
                $unknown[] = $value;
            }
        }

        return [array_values(array_unique($codes)), $unknown];
    }

    /**
     * Region names — what the global controllers want.
     *
     * @return array{0: array<int, string>, 1: array<int, string>} [names, unknown]
     */
    public static function regionNames(string|array $input): array
    {
        return self::regions($input, fn (int $id) => self::REGIONS[$id]);
    }

    /**
     * Region ids — what the player controllers want.
     *
     * @return array{0: array<int, int>, 1: array<int, string>} [ids, unknown]
     */
    public static function regionIds(string|array $input): array
    {
        return self::regions($input, fn (int $id) => $id);
    }

    /**
     * Both directions at once: a caller may send `NA` or `1`, and each endpoint
     * gets whichever its target reads.
     */
    private static function regions(string|array $input, callable $as): array
    {
        $byName = array_change_key_case(array_flip(self::REGIONS));
        $resolved = [];
        $unknown = [];

        foreach (self::split($input) as $value) {
            $value = trim($value);
            $key = mb_strtolower($value);

            if (isset($byName[$key])) {
                $resolved[] = $as($byName[$key]);
            } elseif (ctype_digit($value) && isset(self::REGIONS[(int) $value])) {
                $resolved[] = $as((int) $value);
            } else {
                $unknown[] = $value;
            }
        }

        return [array_values(array_unique($resolved, SORT_REGULAR)), $unknown];
    }

    /**
     * Display name and short code both map to the short code. Read from the same
     * table the site's own filters come from, so a new game type needs no change
     * here — only an entry in GAME_TYPES if it should be offered.
     *
     * @return array<string, string>
     */
    private static function gameTypeLookup(): array
    {
        return Cache::remember('api_game_type_lookup', self::CACHE_SECONDS, function () {
            $lookup = [];

            foreach (GameType::whereIn('short_name', self::GAME_TYPES)->get() as $type) {
                $lookup[mb_strtolower($type->short_name)] = $type->short_name;

                if (filled($type->name)) {
                    $lookup[mb_strtolower($type->name)] = $type->short_name;
                }
            }

            return $lookup;
        });
    }

    /** @return array<int, string> */
    private static function split(string|array $input): array
    {
        $values = is_array($input) ? $input : explode(',', $input);

        return array_filter(array_map('trim', $values), fn ($value) => $value !== '');
    }
}
