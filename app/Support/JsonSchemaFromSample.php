<?php

namespace App\Support;

/**
 * Describes a real response as a JSON Schema.
 *
 * The API's controllers return untyped arrays, so nothing about a response can be
 * inferred from code. The fixtures are the only accurate description of what each
 * endpoint returns — they are captured from live output and `api:check-fixtures`
 * guarantees one exists for every routed endpoint. Deriving the schema from them
 * keeps the spec honest, and re-capturing an endpoint updates its schema for free.
 */
class JsonSchemaFromSample
{
    /** Beyond this, a list is described by its first entries rather than all of them. */
    private const SAMPLE_LIMIT = 25;

    /** @return array<string, mixed> */
    public static function describe(mixed $value): array
    {
        return match (true) {
            is_bool($value) => ['type' => 'boolean'],
            is_int($value) => ['type' => 'integer'],
            is_float($value) => ['type' => 'number'],
            is_string($value) => ['type' => 'string'],
            $value === null => ['type' => 'null'],
            array_is_list($value) => self::describeList($value),
            default => self::describeObject($value),
        };
    }

    /** @param  array<int, mixed>  $value */
    private static function describeList(array $value): array
    {
        if ($value === []) {
            return ['type' => 'array'];
        }

        $items = array_slice($value, 0, self::SAMPLE_LIMIT);
        $schema = self::describe(array_shift($items));

        foreach ($items as $item) {
            $schema = self::merge($schema, self::describe($item));
        }

        return ['type' => 'array', 'items' => $schema];
    }

    /** @param  array<string, mixed>  $value */
    private static function describeObject(array $value): array
    {
        $properties = [];

        foreach ($value as $key => $child) {
            $properties[(string) $key] = self::describe($child);
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => array_keys($properties),
        ];
    }

    /**
     * Combines two descriptions of the same position.
     *
     * Rows in a list disagree — a nullable field is null in one and a string in
     * the next, and an optional key is simply absent. A property is only required
     * when every sampled row carried it.
     */
    private static function merge(array $a, array $b): array
    {
        if ($a === $b) {
            return $a;
        }

        if (($a['type'] ?? null) === 'object' && ($b['type'] ?? null) === 'object') {
            $properties = $a['properties'] ?? [];

            foreach ($b['properties'] ?? [] as $key => $schema) {
                $properties[$key] = isset($properties[$key])
                    ? self::merge($properties[$key], $schema)
                    : $schema;
            }

            return [
                'type' => 'object',
                'properties' => $properties,
                'required' => array_values(array_intersect($a['required'] ?? [], $b['required'] ?? [])),
            ];
        }

        if (($a['type'] ?? null) === 'array' && ($b['type'] ?? null) === 'array') {
            return isset($a['items'], $b['items'])
                ? ['type' => 'array', 'items' => self::merge($a['items'], $b['items'])]
                : ['type' => 'array'];
        }

        // Differing scalars, most often a nullable column. OpenAPI 3.1 takes a
        // list of types, so this stays a single schema rather than a oneOf.
        $types = array_values(array_unique(array_merge(
            (array) ($a['type'] ?? []),
            (array) ($b['type'] ?? []),
        )));

        return count($types) === 1 ? ['type' => $types[0]] : ['type' => $types];
    }
}
