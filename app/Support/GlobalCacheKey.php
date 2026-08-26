<?php

namespace App\Support;

/**
 * Cache keys for the global statistics queries.
 *
 * These queries take minutes, and the whole point of caching them is that
 * everyone asking the same question shares one answer — the site warming an
 * entry for an API caller, and the other way round. That only holds if the same
 * question produces the same key no matter how it arrived, which is what the
 * normalising here is for.
 *
 * It did not hold before. The key hashed the request exactly as it came in, so
 * it also hashed `api_token`: every API key had a private copy of every entry,
 * shared with nobody and never with the site.
 *
 * Lives outside the controllers because `GlobalDataService` writes into the same
 * `GlobalHeroStats` key space that `GlobalHeroStatsController` does. Two copies
 * of this logic drifting apart would quietly split that space in half.
 */
class GlobalCacheKey
{
    /**
     * Transport rather than question: who is asking, and in what format. Neither
     * changes the answer, and `mode=csv` should not recompute minutes of work to
     * hand back data already cached as JSON.
     */
    private const IGNORED = ['api_token', 'mode'];

    /**
     * @param  array<int, int|string>  $gameVersionIds
     * @param  array<string, mixed>  $parameters  Usually `$request->all()`.
     */
    public static function for(string $prefix, array $gameVersionIds, array $parameters): string
    {
        return $prefix.'|'.implode(',', $gameVersionIds).'|'.hash('sha256', json_encode(self::normalize($parameters)));
    }

    /**
     * The same key, for a different set of parameters.
     *
     * A key is `prefix|versions|hash`, and only the hash depends on the parameters
     * — so one query's key can be rebuilt for a variation of it without knowing the
     * prefix or resolving the game versions a second time. That is what lets a
     * `group_by_map` fan-out land on exactly the keys the single-map query already
     * writes, wherever it is called from.
     *
     * Safe because the format it takes apart is the one `for()` puts together, four
     * lines up. Nothing outside this class assembles one.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function withParameters(string $key, array $parameters): string
    {
        $separator = strrpos($key, '|');

        if ($separator === false) {
            return $key;
        }

        return substr($key, 0, $separator + 1).hash('sha256', json_encode(self::normalize($parameters)));
    }

    /**
     * What identifies a query, as opposed to how it happened to be asked for.
     *
     * Null, empty string and absent all mean "not filtered" to these controllers
     * but are three different hashes, and the site posts nulls where the API omits
     * the key entirely — so without dropping them the two could never agree.
     * Order carries no meaning in either the keys or the filter lists, and JSON
     * encoding preserves both.
     *
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public static function normalize(array $parameters): array
    {
        foreach (self::IGNORED as $key) {
            unset($parameters[$key]);
        }

        return self::sorted($parameters);
    }

    /**
     * @param  array<mixed>  $parameters
     * @return array<mixed>
     */
    private static function sorted(array $parameters): array
    {
        $normalized = [];

        foreach ($parameters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $normalized[$key] = is_array($value) ? self::sorted($value) : $value;
        }

        if (array_is_list($normalized)) {
            sort($normalized);
        } else {
            ksort($normalized);
        }

        return $normalized;
    }
}
