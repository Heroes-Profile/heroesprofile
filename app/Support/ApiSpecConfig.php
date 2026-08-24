<?php

namespace App\Support;

/**
 * Resolves an endpoint's declared parameters out of `config/api_spec.php`.
 *
 * Both the generated specification and the request handling read parameter
 * arity from here, so the two cannot describe the same endpoint differently.
 * Before this, the OpenAPI document said `game_type` was one of six values
 * while the code split it on commas, and a filter that became multi-select on
 * the site had to be remembered in two files.
 *
 * `multi` is the site's own distinction: the filters `Filters.vue` renders as
 * `multi-select-filter` are the ones marked here.
 */
class ApiSpecConfig
{
    /**
     * One endpoint's parameters, after its `uses` sets, `except` removals and
     * own `parameters` block are applied.
     *
     * @param  array<string, mixed>  $endpoint
     * @param  array<string, mixed>  $config
     * @return array<string, array<string, mixed>>
     */
    public static function resolve(array $endpoint, array $config): array
    {
        $declared = [];

        foreach ((array) ($endpoint['uses'] ?? []) as $set) {
            $declared = array_merge($declared, $config[$set] ?? []);
        }

        // `uses` pulls in a whole set, but not every endpoint reads every member
        // of it.
        foreach ((array) ($endpoint['except'] ?? []) as $unused) {
            unset($declared[$unused]);
        }

        return array_merge($declared, $endpoint['parameters'] ?? []);
    }

    /**
     * The parameters one route accepts as a comma-separated list.
     *
     * An endpoint that declares its own parameters rather than `uses`-ing a set
     * gets whatever it declared and nothing more — which is how the leaderboard
     * keeps every parameter scalar without stating an exception anywhere.
     *
     * @return array<int, string>
     */
    public static function multiForRoute(?string $routeName): array
    {
        $config = config('api_spec');
        $endpoint = $config['endpoints'][$routeName] ?? null;

        if ($endpoint === null) {
            return [];
        }

        return array_keys(array_filter(
            self::resolve($endpoint, $config),
            fn ($spec) => (bool) ($spec['multi'] ?? false)
        ));
    }
}
