<?php

namespace App\Services;

/**
 * config/faq.php in the visitor's language, from lang/faq/{locale}.json (filled by `translations:faq`).
 *
 * Items are keyed by a hash of the English question and answer, so an edited item falls back to
 * English until it's translated again, rather than showing an outdated translation.
 */
class LocalizedFaq
{
    public static function hash(array $item): string
    {
        return substr(sha1($item['q']."\n".$item['a']), 0, 16);
    }

    public static function path(string $locale): string
    {
        return lang_path('faq/'.$locale.'.json');
    }

    /** @return array{categories: array<string, string>, items: array<string, array{q: string, a: string}>} */
    public static function load(string $locale): array
    {
        $path = self::path($locale);
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return [
            'categories' => $data['categories'] ?? [],
            'items' => $data['items'] ?? [],
        ];
    }

    public static function localize(array $categories, string $locale): array
    {
        if ($locale === 'en') {
            return $categories;
        }

        $strings = self::load($locale);

        foreach ($categories as &$category) {
            $category['name'] = $strings['categories'][$category['name']] ?? $category['name'];

            foreach ($category['items'] as &$item) {
                $translated = $strings['items'][self::hash($item)] ?? null;

                if ($translated !== null) {
                    $item['q'] = $translated['q'];
                    $item['a'] = $translated['a'];
                }
            }
        }

        return $categories;
    }
}
