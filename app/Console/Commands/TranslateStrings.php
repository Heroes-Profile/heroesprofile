<?php

namespace App\Console\Commands;

use Anthropic\Client;
use Anthropic\Messages\RawContentBlockDeltaEvent;
use Anthropic\Messages\TextDelta;
use App\Http\Controllers\Api\External\MatchController;
use App\Services\Api\ApiResponseLocalizer;
use App\Services\GlobalQueryService;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Machine-translates site text that has no translation yet into lang/{locale}.json.
 *
 * Keys are found in the code: $t(...) / translate(...) in resources/js, __(...) in the Blade views and app/, and
 * the text props that shared components translate themselves (button text, headings, panel and modal titles,
 * page info text).
 * Existing translations are never changed, so hand corrections stay.
 */
class TranslateStrings extends Command
{
    protected $signature = 'translations:strings {locale?* : Locales to fill (default: every supported locale)} {--dry-run : Only list what is missing} {--check : List what is missing and fail if anything is (CI)}';

    protected $description = 'Machine-translate missing site text into lang/{locale}.json';

    private const MODEL = 'claude-opus-5-5';

    private const BATCH = 60;

    // Nothing skipped: the API portal, admin pages and legal pages are translated too, the legal pages with a
    // note that the English text governs (LegalTranslationNotice)
    private const SKIP_PATHS = [];

    public function handle(): int
    {
        $keys = $this->keysInCode();
        $supported = array_diff(array_keys(config('app.supported_locales')), ['en']);
        $locales = $this->argument('locale') ?: $supported;

        $listOnly = $this->option('dry-run') || $this->option('check');
        $missingTotal = 0;

        $client = null;
        if (! $listOnly) {
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

            $missingTotal += count($missing);

            $this->line("{$locale}: ".count($missing).' missing');
            if ($listOnly || $missing === []) {
                if ($listOnly && ($this->option('check') || $this->output->isVerbose())) {
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

        if ($this->option('check') && $missingTotal > 0) {
            $this->error("{$missingTotal} translations missing. Run 'php artisan translations:strings' and commit the lang/*.json changes.");

            return self::FAILURE;
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
            '/:(?:text|heading|title|subtitle|label|all-label|button-text|buttonText|label-text|labelText|link-text|linkText)="\'([^\']+)\'"/',
            '/:(?:tab1text|tab2text|tab3text|infoText1|infoText2|input)="\'([^\']+)\'"/',
            // Page info text set in data() for page-heading (Infobox runs it through $t); a plain literal, not one built with +
            '/\binfoText\d?:\s*(?:"([^"\\\\]+)"|\'([^\'\\\\]+)\')\s*[,}\n]/',
        ];

        $finder = (new Finder)->files()->in([resource_path('js'), resource_path('views')])->name(['*.vue', '*.js', '*.blade.php']);
        $keys = [];

        // __() in the controllers and services: messages they send to the page
        $app = (new Finder)->files()->in(app_path())->name('*.php')->notPath('Console/Commands');

        foreach ($app as $file) {
            preg_match_all($patterns[0], $file->getContents(), $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $key = collect(array_slice($match, 1))->first(fn ($group) => $group !== '');
                if ($key !== null && preg_match('/[A-Za-z]/', $key)) {
                    $keys[stripslashes($key)] = true;
                }
            }
        }

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

            // Plain text props on the shared components, every one on the tag (a panel-box can have a subtitle and link-text)
            preg_match_all('/<(?:custom-button|page-heading|panel-box|modal-box|group-box|tab-button|search-input)\b[^>]*>/', $source, $tags);
            foreach ($tags[0] as $tag) {
                preg_match_all('/\s(?:text|heading|title|subtitle|link-text|linkText|tab1text|tab2text|tab3text|placeholder|label)="([A-Z][^"{]*)"/', $tag, $props);
                foreach ($props[1] as $key) {
                    $keys[$key] = true;
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

        // API endpoint categories, stored in the registry table and shown through $t on the API pages
        preg_match_all("/^\s*\['[a-z_]+', '[^']*', '([^']+)',/m", (string) file_get_contents(database_path('seeders/ApiEndpointRegistrySeeder.php')), $groups);
        foreach ($groups[1] as $group) {
            $keys[$group] = true;
        }

        // API Docs: section names, summaries and descriptions from the committed spec (ApiDocs, ApiSchemaTree)
        $spec = json_decode((string) file_get_contents(public_path('spec/heroesprofile-v1.json')), true) ?: [];
        foreach ($spec['tags'] ?? [] as $tag) {
            $keys[$tag['name']] = true;
        }
        array_walk_recursive($spec['paths'], function ($value, $field) use (&$keys) {
            if (in_array($field, ['summary', 'description', 'x-rate-limit'], true) && is_string($value) && $value !== '') {
                $keys[$value] = true;
            }
        });
        array_walk_recursive($spec['components'], function ($value, $field) use (&$keys) {
            if ($field === 'description' && is_string($value) && $value !== '') {
                $keys[$value] = true;
            }
        });

        // API Docs Variables: what each one is for, and the meanings that are words rather than codes
        $variables = (string) file_get_contents(app_path('Support/ApiVariables.php'));
        preg_match_all("/'(?:used_by|summary)' => '((?:[^'\\\\]|\\\\.)*)'/", $variables, $texts);
        preg_match_all("/'\\d' => '([^']*\\s[^']*)'/", $variables, $labels);
        preg_match_all("/'\\d{5}' => '([^']*\\s[^']*)'/", (string) file_get_contents(app_path('Support/PartyCombinations.php')), $parties);
        foreach ([...$texts[1], ...$labels[1], ...$parties[1]] as $text) {
            $keys[stripslashes($text)] = true;
        }

        // ?lang= on the API: award names and descriptions, roles and game types (ApiResponseLocalizer),
        // and error messages kept in constants because jobs store them in English
        $awards = (string) file_get_contents(database_path('seeders/AwardsTableSeeder.php'));
        preg_match_all("/'(?:title|description)' => '((?:[^'\\\\]|\\\\.)*)'/", $awards, $awardTexts);
        foreach ([...$awardTexts[1], ...ApiResponseLocalizer::TERMS, GlobalQueryService::TOO_LARGE_MESSAGE, ...MatchController::DOWNLOAD_ERRORS] as $text) {
            $keys[stripslashes($text)] = true;
        }

        // Built at runtime: GlobalDataService::localizeFilterLabels()
        foreach ([':year Season :season', 'Season :season', 'Preseason'] as $key) {
            $keys[$key] = true;
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
            ."Keep placeholders like :count or :battletag exactly as written. Text in backticks (`like_this`) is code: copy it exactly, backticks included, untranslated. Keep URLs, paths, parameter and field names, error codes, HTTP statuses and version numbers as written.\n"
            ."Don't translate BattleTags, product names (Heroes Profile, Patreon, Battle.net, GitHub, Discord, Twitch), or hero, map and talent names.\n"
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
