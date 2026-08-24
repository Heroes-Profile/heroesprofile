<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * `?mode=csv` carried over from the old API, which its spreadsheet users rely on.
 *
 * Streamed rather than built in memory, so a large result set costs one row of
 * memory rather than the whole file.
 */
class CsvResponse
{
    /** @param  iterable<int, array<string, mixed>>  $rows */
    public static function stream(iterable $rows, string $filename): StreamedResponse
    {
        $rows = is_array($rows) ? $rows : iterator_to_array($rows);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            $headings = [];

            foreach ($rows as $row) {
                $row = Arr::dot($row);

                if ($headings === []) {
                    $headings = array_keys($row);
                    fputcsv($handle, $headings);
                }

                // Keyed by the first row's headings so a row with extra or missing
                // fields cannot shift every later column out of alignment.
                fputcsv($handle, array_map(
                    fn (string $heading) => $row[$heading] ?? null,
                    $headings
                ));
            }

            fclose($handle);
        }, self::filename($filename), [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Rows out of a fixture, so `?mode=csv` returns CSV on the fixture path too
     * rather than silently falling back to JSON.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public static function rowsFromPayload(array $payload): ?array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        // Single-key envelope, e.g. {"maps": [...]}.
        $values = array_values($payload);

        if (count($values) === 1 && is_array($values[0]) && array_is_list($values[0])) {
            return $values[0];
        }

        return null;
    }

    private static function filename(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9\-_]/', '', str_replace(' ', '-', $name));

        return 'hp-'.$name.'-'.now()->format('Ymd').'.csv';
    }
}
