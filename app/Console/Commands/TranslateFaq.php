<?php

namespace App\Console\Commands;

use Anthropic\Client;
use Anthropic\Messages\RawContentBlockDeltaEvent;
use Anthropic\Messages\TextDelta;
use App\Services\LocalizedFaq;
use Illuminate\Console\Command;

/**
 * Machine-translates config/faq.php into lang/faq/{locale}.json. Only items that are new or edited
 * since the last run are sent; items removed from the FAQ are dropped from the files.
 */
class TranslateFaq extends Command
{
    protected $signature = 'translations:faq {locale?* : Locales to fill (default: every supported locale)} {--fresh : Translate every item again}';

    protected $description = 'Machine-translate the FAQ into lang/faq/{locale}.json';

    private const MODEL = 'claude-opus-5-5';

    public function handle(): int
    {
        $apiKey = config('services.help_chat.anthropic_api_key');
        if (! $apiKey) {
            $this->error('HEROESPROFILE_CHAT_ANTHROPIC_API_KEY is not set.');

            return self::FAILURE;
        }

        $client = new Client(apiKey: $apiKey);
        $supported = array_diff(array_keys(config('app.supported_locales')), ['en']);
        $locales = $this->argument('locale') ?: $supported;

        foreach ($locales as $locale) {
            if (! in_array($locale, $supported, true)) {
                $this->error("Unsupported locale: {$locale}");

                continue;
            }

            $this->translateLocale($client, $locale);
        }

        return self::SUCCESS;
    }

    private function translateLocale(Client $client, string $locale): void
    {
        $language = config('app.supported_locales')[$locale];
        $existing = $this->option('fresh') ? ['categories' => [], 'items' => []] : LocalizedFaq::load($locale);
        $result = ['categories' => [], 'items' => []];

        foreach (config('faq.categories') as $category) {
            $pending = [];

            foreach ($category['items'] as $item) {
                $hash = LocalizedFaq::hash($item);

                if (isset($existing['items'][$hash])) {
                    $result['items'][$hash] = $existing['items'][$hash];
                } else {
                    $pending[$hash] = ['q' => $item['q'], 'a' => $item['a']];
                }
            }

            $name = $existing['categories'][$category['name']] ?? null;

            if ($pending === [] && $name !== null) {
                $result['categories'][$category['name']] = $name;

                continue;
            }

            $this->line("{$locale}: {$category['name']} (".count($pending).' items)');

            $translated = $this->request($client, $locale, $language, [
                'category' => $category['name'],
                'items' => (object) $pending,
            ]);

            if ($translated === null) {
                $this->warn("{$locale}: {$category['name']} failed, left in English");
                if ($name !== null) {
                    $result['categories'][$category['name']] = $name;
                }

                continue;
            }

            $result['categories'][$category['name']] = $translated['category'] ?? $name ?? $category['name'];

            foreach ($pending as $hash => $item) {
                $row = $translated['items'][$hash] ?? null;

                if (is_array($row) && is_string($row['q'] ?? null) && is_string($row['a'] ?? null)) {
                    $result['items'][$hash] = ['q' => $row['q'], 'a' => $row['a']];
                }
            }
        }

        $path = LocalizedFaq::path($locale);
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
        $this->info("{$locale}: ".count($result['items']).' items written');
    }

    private function request(Client $client, string $locale, string $language, array $payload): ?array
    {
        // The site's own translations keep terms (roles, game modes, page names) consistent with the rest of the page
        $glossary = json_decode((string) @file_get_contents(lang_path($locale.'.json')), true) ?: [];

        $system = "You translate the FAQ of Heroes Profile, a Heroes of the Storm statistics site, from English into {$language} ({$locale}).\n"
            ."Return only JSON with the same shape as the input: {\"category\": \"...\", \"items\": {\"<key>\": {\"q\": \"...\", \"a\": \"...\"}}}, with every key kept.\n"
            ."Answers are HTML: keep every tag, attribute and URL exactly as it is and translate only the visible text. Don't translate BattleTags, code, file paths, product names (Heroes Profile, Patreon, Battle.net, GitHub, Discord), or hero, map and talent names.\n"
            ."Use Blizzard's official {$language} terms for game concepts. Where the glossary below has a term, use it.\n\n"
            .'Glossary: '.json_encode($glossary, JSON_UNESCAPED_UNICODE);

        // Streamed: a category's answers can run to tens of thousands of tokens
        $text = '';
        try {
            $stream = $client->messages->createStream(
                model: self::MODEL,
                maxTokens: 64000,
                system: [['type' => 'text', 'text' => $system, 'cacheControl' => ['type' => 'ephemeral']]],
                messages: [['role' => 'user', 'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]],
            );

            foreach ($stream as $event) {
                if ($event instanceof RawContentBlockDeltaEvent && $event->delta instanceof TextDelta) {
                    $text .= $event->delta->text;
                }
            }
        } catch (\Throwable $e) {
            $this->warn($e->getMessage());

            return null;
        }

        $text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($text));
        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }
}
