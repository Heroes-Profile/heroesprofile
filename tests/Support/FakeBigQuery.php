<?php

namespace Tests\Support;

use App\Services\AnalyticsQueryService;

/**
 * BigQuery without Google. Replaces only run(), the one place the app talks to BigQuery:
 * every query the app builds is recorded with its parameters, and answered with the rows a
 * test gives for that query name (none by default). Caching, filters and post-processing stay real.
 */
class FakeBigQuery extends AnalyticsQueryService
{
    /** @var array<int, array{queries: array<string, string>, params: array<string, mixed>}> */
    public array $calls = [];

    /** @var array<string, array<int, array<string, mixed>>> rows to answer with, by query name */
    public array $rows = [];

    public function run(array $queries, array $params): array
    {
        $this->calls[] = ['queries' => $queries, 'params' => $params];

        return array_map(fn ($sql) => [], $queries) === []
            ? []
            : array_combine(array_keys($queries), array_map(fn ($name) => $this->rows[$name] ?? [], array_keys($queries)));
    }

    /** @return array<int, string> every query name asked for, in order */
    public function queryNames(): array
    {
        return array_merge(...array_map(fn ($call) => array_keys($call['queries']), $this->calls ?: [['queries' => []]]));
    }
}
