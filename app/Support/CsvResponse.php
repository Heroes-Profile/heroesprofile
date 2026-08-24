<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * `?mode=csv` carried over from the old API, which its spreadsheet users rely on.
 *
 * The old site hand-wrote a flattening per endpoint, which is why only fourteen of
 * them ever supported it. This does the job once, off the response body, so every
 * endpoint that answers with JSON answers with CSV too.
 */
class CsvResponse
{
    /** Column naming the group when rows come from several keyed collections. */
    private const GROUP_COLUMN = 'group';

    /** Column naming the key when rows come from an object keyed by name. */
    private const KEY_COLUMN = 'key';

    /** @param  iterable<int, array<string, mixed>>  $rows */
    public static function stream(iterable $rows, string $filename): StreamedResponse
    {
        $rows = is_array($rows) ? $rows : iterator_to_array($rows);

        // Flattened up front so the headings can be the union of every row's keys.
        // Taking them from the first row alone drops any column that only appears
        // later, which is common once nested objects differ between records.
        $flat = array_map(fn ($row) => Arr::dot(is_array($row) ? $row : ['value' => $row]), $rows);

        $headings = [];

        foreach ($flat as $row) {
            foreach (array_keys($row) as $key) {
                $headings[$key] = true;
            }
        }

        $headings = array_keys($headings);

        return response()->streamDownload(function () use ($flat, $headings) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headings);

            foreach ($flat as $row) {
                // Keyed by the headings so a row with extra or missing fields cannot
                // shift every later column out of alignment.
                fputcsv($handle, array_map(
                    fn (string $heading) => self::cell($row[$heading] ?? null),
                    $headings
                ));
            }

            fclose($handle);
        }, self::filename($filename), [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Rows out of a decoded JSON body.
     *
     * Several shapes come back from these endpoints, and each needs a different reading
     * to become a table. Returns null only when there is nothing tabular at all.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public static function rowsFromPayload(array $payload): ?array
    {
        if ($payload === []) {
            return null;
        }

        // 1. Already a list of records.
        if (array_is_list($payload)) {
            return array_map(fn ($row) => is_array($row) ? $row : ['value' => $row], $payload);
        }

        // 2. A single key wrapping everything, where the value is itself structured:
        //    `{talents: {...}}`. The wrapper says nothing a table needs, so read
        //    through it. Each step removes a level, so this terminates.
        if (count($payload) === 1) {
            $inner = reset($payload);

            if (is_array($inner) && $inner !== []) {
                return self::rowsFromPayload($inner);
            }
        }

        $lists = array_filter($payload, fn ($value) => is_array($value) && array_is_list($value));

        // Scalars sitting beside a collection describe it rather than belonging to
        // any one row, so they repeat on every row instead of being dropped.
        $context = array_filter(
            array_diff_key($payload, $lists),
            fn ($value) => ! is_array($value)
        );

        // 3. A paginator, or anything else naming its payload `data`. Named
        //    explicitly because these carry a second list (`links`) that would
        //    otherwise make the primary collection ambiguous.
        if (isset($lists['data'])) {
            return self::rowsFrom($lists['data'], $context);
        }

        // 4. One collection alongside scalars: an envelope, or a result set with its
        //    query echoed back.
        if (count($lists) === 1) {
            return self::rowsFrom(reset($lists), $context);
        }

        // 5. Several collections, so the key is a dimension rather than a wrapper:
        //    ally/enemy/combined, or a talent tier per level. Rows from all of them,
        //    with the key kept as a column.
        if (count($lists) > 1) {
            $rows = [];

            foreach ($lists as $group => $items) {
                foreach ($items as $item) {
                    $rows[] = [self::GROUP_COLUMN => $group]
                        + $context
                        + (is_array($item) ? $item : ['value' => $item]);
                }
            }

            return $rows;
        }

        // 6. An object keyed by name whose values are records: one row each.
        if ($lists === [] && self::isMapOfArrays($payload)) {
            $rows = [];

            foreach ($payload as $key => $value) {
                $rows[] = [self::KEY_COLUMN => $key] + $value;
            }

            return $rows;
        }

        // 7. A single record. One row, flattened.
        return [$payload];
    }

    /**
     * @param  array<int, mixed>  $items
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    private static function rowsFrom(array $items, array $context): array
    {
        return array_map(
            fn ($item) => $context + (is_array($item) ? $item : ['value' => $item]),
            $items
        );
    }

    /** @param  array<string, mixed>  $payload */
    private static function isMapOfArrays(array $payload): bool
    {
        foreach ($payload as $value) {
            if (! is_array($value)) {
                return false;
            }
        }

        return true;
    }

    /** Scalars pass through; anything still nested is rendered rather than dropped. */
    private static function cell(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }

    private static function filename(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9\-_]/', '', str_replace([' ', '/'], '-', $name));

        return 'hp-'.$name.'-'.now()->format('Ymd').'.csv';
    }
}
