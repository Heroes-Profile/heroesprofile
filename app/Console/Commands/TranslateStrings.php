<?php

namespace App\Console\Commands;

use Anthropic\Client;
use Anthropic\Messages\RawContentBlockDeltaEvent;
use Anthropic\Messages\TextDelta;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Machine-translates site text that has no translation yet into lang/{locale}.json.
 *
 * Keys are found in the code: $t(...) / translate(...) in resources/js, __(...) in the Blade views, and the
 * text props that shared components translate themselves (button text, headings, panel and modal titles).
 * Existing translations are never changed, so hand corrections stay.
 */
class TranslateStrings extends Command
{
    protected $signature = 'translations:strings {locale?* : Locales to fill (default: every supported locale)} {--dry-run : Only list what is missing}';

    protected $description = 'Machine-translate missing site text into lang/{locale}.json';

    private const MODEL = 'claude-opus-5-5';

    private const BATCH = 60;

    // API pages and their docs stay English (decision 10), as do the legal pages
    private const SKIP_PATHS = ['/Api/', '/api/', 'Admin', 'PrivacyPolicy', 'TermsOfService'];

    public function handle(): int
    {
        $keys = $this->keysInCode();
        $supported = array_diff(array_keys(config('app.supported_locales')), ['en']);
        $locales = $this->argument('locale') ?: $supported;

        $client = null;
        if (! $this->option('dry-run')) {
            $apiKey = config('services.help_chat.anthropic_api_key');
            if (! $apiKey) {
                $this->error('HEROESPROFILE_CHAT_ANTHROPIC_API_KEY is not set.');

                return self::FAILURE;
            }
            $client = new Client(apiKey: $apiKey);
        }

        foreach ($locales as $locale) {
            if (! in_array($locale, $supported, true)) {
                $this->error("Unsupported locale: {$locale}");

                continue;
            }

            $path = lang_path($locale.'.json');
            $strings = is_file($path) ? (json_decode((string) file_get_contents($path), true) ?: []) : [];
            $missing = array_values(array_filter($keys, fn ($key) => ! isset($strings[$key])));

            $this->line("{$locale}: ".count($missing).' missing');
            if ($this->option('dry-run') || $missing === []) {
                if ($this->option('dry-run') && $this->output->isVerbose()) {
                    foreach ($missing as $key) {
                        $this->line('  '.$key);
                    }
                }

                continue;
            }

            // The starting file as glossary for every batch, so the cached prompt stays the same
            $glossary = $strings;

            foreach (array_chunk($missing, self::BATCH) as $batch) {
                $translated = $this->request($client, $locale, $glossary, $batch);

                foreach ($batch as $key) {
                    $value = $translated[$key] ?? null;

                    if (is_string($value) && $value !== '' && $this->placeholders($value) === $this->placeholders($key)) {
                        $strings[$key] = $value;
                    }
                }

                // Saved after every batch, so a failure part way keeps what's done
                uksort($strings, 'strcasecmp');
                file_put_contents($path, json_encode($strings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");

                $done = count(array_filter($missing, fn ($key) => isset($strings[$key])));
                $this->line("{$locale}: {$done}/".count($missing));
            }

            $left = count(array_filter($keys, fn ($key) => ! isset($strings[$key])));
            $this->info("{$locale}: done".($left ? ", {$left} still missing (run again)" : ''));
        }

        return self::SUCCESS;
    }

    /** @return array<int, string> */
    private function keysInCode(): array
    {
        $patterns = [
            // $t('...'), this.$t("..."), translate(`...`), __('...')
            '/(?:\$t|translate|__)\(\s*(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)"|`([^`$]*)`)/',
            // Props the shared components pass through $t
            '/:(?:text|heading|title|label|all-label|button-text|buttonText|label-text|labelText)="\'([^\']+)\'"/',
            '/<(?:custom-button|page-heading|panel-box|modal-box)\b[^>]*?\s(?:text|heading|title)="([A-Z][^"{]*)"/',
        ];

        $finder = (new Finder)->files()->in([resource_path('js'), resource_path('views')])->name(['*.vue', '*.js', '*.blade.php']);
        $keys = [];

        foreach ($finder as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (collect(self::SKIP_PATHS)->contains(fn ($skip) => str_contains($path, $skip))) {
                continue;
            }

            $source = $file->getContents();
            foreach ($patterns as $pattern) {
                preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $key = collect(array_slice($match, 1))->first(fn ($group) => $group !== '');
                    if ($key !== null && preg_match('/[A-Za-z]/', $key)) {
                        $keys[stripslashes($key)] = true;
                    }
                }
            }
        }

        // The FAQ page's site guide, translated through __() in FaqMatcher::pages()
        foreach (config('faq.pages') as $section) {
            foreach ([$section['name'], $section['note'] ?? null, $section['viewer_note'] ?? null] as $text) {
                if ($text) {
                    $keys[$text] = true;
                }
            }
            foreach ($section['items'] as $item) {
                $keys[$item['name']] = true;
                $keys[$item['description']] = true;
            }
        }

        return array_keys($keys);
    }

    /** @return array<int, string> */
    private function placeholders(string $text): array
    {
        preg_match_all('/:[a-z]+/', $text, $matches);
        sort($matches[0]);

        return $matches[0];
    }

    /** @return array<string, string>|null */
    private function request(Client $client, string $locale, array $existing, array $batch): ?array
    {
        $language = config('app.supported_locales')[$locale];

        $system = "You translate the interface of Heroes Profile, a Heroes of the Storm statistics site, from English into {$language} ({$locale}).\n"
            ."The input is a JSON object whose keys are English strings. Return only a JSON object with the same keys and the translation of each as the value.\n"
            ."Keep placeholders like :count or :battletag exactly as written. Don't translate BattleTags, product names (Heroes Profile, Patreon, Battle.net, GitHub, Discord, Twitch), or hero, map and talent names.\n"
            ."These are short UI labels, headings and help text: keep them concise and natural for a site UI. Use Blizzard's official {$language} terms for game concepts.\n"
            .'Some keys are sentence fragments that run straight into a link or a value on the page (they often end without punctuation, e.g. "Create your own builds at"); translate them so they still read naturally when followed by it.'."\n"
            ."The site's existing translations follow; match their wording for anything they share.\n\n"
            .json_encode($existing, JSON_UNESCAPED_UNICODE);

        $text = '';
        try {
            $stream = $client->messages->createStream(
                model: self::MODEL,
                maxTokens: 64000,
                system: [['type' => 'text', 'text' => $system, 'cacheControl' => ['type' => 'ephemeral']]],
                messages: [['role' => 'user', 'content' => json_encode(array_combine($batch, $batch), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]],
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
