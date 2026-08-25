<?php

namespace App\Support;

use Illuminate\Cache\DatabaseStore;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Facades\Cache;

/**
 * Reads many cache entries in one query.
 *
 * `Cache::many()` reads like a batch operation and is not one: the database store
 * takes it from `RetrievesMultipleKeys`, a plain loop of single `get()` calls.
 * Fine for a handful of keys, not fine for a batch query, where the state of every
 * child is re-read on each poll and again each time one of them finishes.
 *
 * Mirrors `DatabaseStore::get()` — same prefix, same expiry rule, same
 * unserialisation — and defers to the store's own `many()` if the cache is ever
 * pointed at a driver this does not describe.
 */
class DatabaseCacheReader
{
    /**
     * Which of these keys hold a live entry, without transferring any values.
     *
     * The values here are whole query results; a batch only needs to know whether
     * each one has landed yet, and reading ninety of them to answer that is the
     * expensive way to ask.
     *
     * @param  array<int, string>  $keys
     * @return array<string, bool> Keyed as given.
     */
    public static function existing(array $keys): array
    {
        $present = array_flip(array_keys(self::rows($keys, ['key', 'expiration'])));

        $results = [];

        foreach ($keys as $key) {
            $results[$key] = isset($present[$key]);
        }

        return $results;
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, mixed> Keyed as given; missing and expired entries are null.
     */
    public static function many(array $keys): array
    {
        $store = self::store();

        if ($store === null) {
            return $keys === [] ? [] : Cache::store('database')->many($keys);
        }

        $connection = $store->getConnection();
        $found = [];

        foreach (self::rows($keys, ['key', 'value', 'expiration']) as $key => $row) {
            $found[$key] = self::unserialize($connection, $row->value);
        }

        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $found[$key] ?? null;
        }

        return $results;
    }

    /**
     * The unexpired rows for these keys, keyed by unprefixed key.
     *
     * Expired rows are skipped rather than deleted. `DatabaseStore::get()` deletes
     * them as it goes, but that is its business — this is a read, and treating an
     * expired row as absent gives the same answer.
     *
     * @param  array<int, string>  $keys
     * @param  array<int, string>  $columns
     * @return array<string, object>
     */
    private static function rows(array $keys, array $columns): array
    {
        $store = self::store();

        if ($keys === [] || $store === null) {
            return [];
        }

        $prefix = $store->getPrefix();

        $rows = $store->getConnection()
            ->table(config('cache.stores.database.table'))
            ->whereIn('key', array_map(static fn ($key) => $prefix.$key, $keys))
            ->get($columns);

        $now = time();
        $live = [];

        foreach ($rows as $row) {
            $row = is_array($row) ? (object) $row : $row;

            if ($now >= $row->expiration) {
                continue;
            }

            $live[substr($row->key, strlen($prefix))] = $row;
        }

        return $live;
    }

    private static function store(): ?DatabaseStore
    {
        $store = Cache::store('database')->getStore();

        return $store instanceof DatabaseStore ? $store : null;
    }

    /** As `DatabaseStore::unserialize()`, which is protected. */
    private static function unserialize($connection, string $value): mixed
    {
        if ($connection instanceof PostgresConnection && ! str_contains($value, ':') && ! str_contains($value, ';')) {
            $value = base64_decode($value);
        }

        return unserialize($value);
    }
}
