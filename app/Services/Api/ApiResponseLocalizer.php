<?php

namespace App\Services\Api;

use App\Services\LocalizedGameStrings;
use Illuminate\Contracts\Translation\Translator;

/**
 * Adds `<field>_localized` next to the English names in an API response, for `?lang=`.
 *
 * English stays untouched: callers send these names back as parameters and match on
 * them. A localized field is only added when a translation exists. Object keys that
 * are names (talents keyed by hero, ratings keyed by game type) stay English.
 */
class ApiResponseLocalizer
{
    /** Fields whose value can be a hero, map, role or game type name. */
    private const NAME_FIELDS = ['name', 'hero', 'hero_name', 'game_map', 'map', 'map_name', 'role', 'new_role', 'most_played_role', 'game_type'];

    /** Role and game type names, translated through lang/{locale}.json like the site. */
    public const TERMS = [
        'Tank', 'Bruiser', 'Healer', 'Support', 'Melee Assassin', 'Ranged Assassin',
        'Warrior', 'Assassin', 'Specialist', 'Multiclass',
        'Quick Match', 'Unranked Draft', 'Hero League', 'Team League', 'Storm League', 'ARAM', 'Brawl', 'Custom',
    ];

    /** @var array{heroes: array<string, string>, maps: array<string, string>} */
    private array $names = ['heroes' => [], 'maps' => []];

    /** @var array<int, array<string, string|null>> */
    private array $talents = [];

    private string $locale = 'en';

    public function __construct(
        private readonly LocalizedGameStrings $strings,
        private readonly Translator $translator,
    ) {}

    public function localize(array $data, string $locale): array
    {
        if ($locale === 'en') {
            return $data;
        }

        $this->locale = $locale;
        $this->names = $this->strings->names($locale);
        $this->talents = $this->strings->talents($locale);

        $this->walk($data);

        return $data;
    }

    private function walk(array &$node): void
    {
        if (isset($node['talent_id'], $node['title'])) {
            $talent = $this->talents[(int) $node['talent_id']] ?? [];
            $this->add($node, 'title', $talent['localized_name'] ?? null);
            $this->add($node, 'description', $talent['localized_description'] ?? null);
        } elseif (isset($node['award_id'], $node['title'])) {
            $this->add($node, 'title', $this->term($node['title']));
            $this->add($node, 'description', $this->term($node['description'] ?? null));
        }

        foreach (self::NAME_FIELDS as $field) {
            if (isset($node[$field]) && is_string($node[$field])) {
                $value = $node[$field];
                $this->add($node, $field, $this->names['heroes'][$value]
                    ?? $this->names['maps'][$value]
                    ?? (in_array($value, self::TERMS, true) ? $this->term($value) : null));
            }
        }

        foreach ($node as &$child) {
            if (is_array($child)) {
                $this->walk($child);
            }
        }
    }

    private function add(array &$node, string $field, ?string $value): void
    {
        if ($value !== null && $value !== '' && ! array_key_exists($field.'_localized', $node)) {
            $node[$field.'_localized'] = $value;
        }
    }

    /** A site translation for $text, or null when there is none. */
    private function term(mixed $text): ?string
    {
        if (! is_string($text) || $text === '') {
            return null;
        }

        $translated = $this->translator->get($text, [], $this->locale);

        return is_string($translated) && $translated !== $text ? $translated : null;
    }
}
