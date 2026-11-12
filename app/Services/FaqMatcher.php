<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

// Finds FAQ entries (config/faq.php) that clearly answer a help chat question, by keyword overlap
class FaqMatcher
{
    // Words that say nothing about what's being asked
    public const STOPWORDS = [
        'a', 'an', 'the', 'and', 'or', 'but', 'if', 'of', 'to', 'in', 'on', 'at', 'for', 'with', 'from', 'by', 'about', 'as', 'into',
        'is', 'are', 'was', 'were', 'be', 'been', 'am', 'do', 'does', 'did', 'done', 'have', 'has', 'had', 'can', 'could', 'will',
        'would', 'should', 'may', 'might', 'must', 'shall', 'i', 'me', 'my', 'mine', 'you', 'your', 'we', 'our', 'us', 'it', 'its',
        'this', 'that', 'these', 'those', 'there', 'here', 'what', 'which', 'who', 'whom', 'when', 'where', 'why', 'how', 'any',
        'some', 'all', 'no', 'not', 'yes', 'so', 'than', 'then', 'too', 'very', 'just', 'also', 'get', 'got', 'see', 'find', 'use',
        'using', 'want', 'need', 'know', 'tell', 'please', 'hi', 'hello', 'hey', 'thanks', 'thank', 'heroes', 'profile',
        'site', 'website', 'page', 'hp', 'one', 'way', 'like', 'feature', 'features', 'show', 'work', 'works',
    ];

    // Share of the question's keywords a result must cover; the user decides if it helped
    private const MIN_COVERAGE = 0.5;

    // Results scoring under this share of the best one are dropped
    private const NEAR_BEST = 0.75;

    private const MAX_RESULTS = 3;

    /**
     * The FAQ answers and site pages that best fit the question, best first.
     *
     * @return array{faq: array<int, array{q: string, a: string, id: ?string}>, pages: array<int, array{name: string, url: string, description: string}>}
     */
    public function search(string $question): array
    {
        $keywords = array_values(array_unique($this->keywords($question)));
        if (! $keywords) {
            return ['faq' => [], 'pages' => []];
        }

        $faq = [];
        foreach (config('faq.categories') as $category) {
            foreach ($category['items'] as $item) {
                $faq[] = $this->score($keywords, $item['q'], strip_tags($item['a']), [
                    'q' => $item['q'],
                    'a' => $item['a'],
                    'id' => $item['id'] ?? null,
                ]);
            }
        }

        $pages = [];
        foreach (config('faq.pages') as $section) {
            foreach ($section['items'] as $page) {
                // Example profile URLs say nothing about the page: leave out the BattleTag and IDs
                $url = preg_replace('#^/Player/[^/]+/\d+/\d+#', '/Player', $page['url']);
                $pages[] = $this->score($keywords, $page['name'].' '.$url, $page['description'], $page);
            }
        }

        return self::forViewer(['faq' => $this->best($faq), 'pages' => $this->best($pages)]);
    }

    // The FAQ's example profile. Logged-in readers get their own in its place.
    public const EXAMPLE_PROFILE = '/Player/Zemill/67280/1';

    public static function viewerProfile(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        $user = Auth::user();

        return '/Player/'.rawurlencode(explode('#', $user->battletag)[0]).'/'.$user->blizz_id.'/'.$user->region;
    }

    public static function forViewer(array $data): array
    {
        $profile = self::viewerProfile();
        if (! $profile) {
            return $data;
        }

        array_walk_recursive($data, function (&$value) use ($profile) {
            if (is_string($value)) {
                $value = str_replace(self::EXAMPLE_PROFILE, $profile, $value);
            }
        });

        return $data;
    }

    // config/faq.php pages for whoever's reading, with the logged-in note when there is one
    public static function pages(): array
    {
        $loggedIn = Auth::check();

        $pages = collect(config('faq.pages'))->map(function ($section) use ($loggedIn) {
            if ($loggedIn && isset($section['viewer_note'])) {
                $section['note'] = $section['viewer_note'];
            }
            unset($section['viewer_note']);

            return $section;
        })->all();

        return self::forViewer($pages);
    }

    // Hits in the title count fully, hits only in the body count half
    private function score(array $keywords, string $title, string $body, array $result): array
    {
        $titleWords = array_flip($this->keywords($title));
        $bodyWords = array_flip($this->keywords($body));

        $titleHits = 0;
        $bodyHits = 0;
        foreach ($keywords as $keyword) {
            if (isset($titleWords[$keyword])) {
                $titleHits++;
            } elseif (isset($bodyWords[$keyword])) {
                $bodyHits++;
            }
        }

        return [
            'coverage' => ($titleHits + 0.5 * $bodyHits) / count($keywords),
            'hits' => $titleHits,
            'bodyHits' => $bodyHits,
            // Ties go to the tightest title
            'precision' => $titleHits / max(1, count($titleWords)),
            'result' => $result,
        ];
    }

    private function best(array $scored): array
    {
        // One stray word in a long answer isn't a match: it needs the title, or two words in the body
        $scored = array_filter($scored, fn ($entry) => $entry['coverage'] >= self::MIN_COVERAGE && ($entry['hits'] > 0 || $entry['bodyHits'] >= 2));
        usort($scored, fn ($x, $y) => [$y['coverage'], $y['hits'], $y['precision']] <=> [$x['coverage'], $x['hits'], $x['precision']]);

        $top = $scored[0]['coverage'] ?? 0;
        $scored = array_filter($scored, fn ($entry) => $entry['coverage'] >= $top * self::NEAR_BEST);

        return array_map(fn ($entry) => $entry['result'], array_slice($scored, 0, self::MAX_RESULTS));
    }

    private function keywords(string $text): array
    {
        preg_match_all('/[a-z0-9]+/', strtolower(html_entity_decode($text, ENT_QUOTES | ENT_HTML5)), $matches);

        $words = [];
        foreach ($matches[0] as $word) {
            if (strlen($word) < 2 || in_array($word, self::STOPWORDS, true)) {
                continue;
            }
            // replays -> replay, matches -> match
            if (strlen($word) > 4 && str_ends_with($word, 'es') && ! str_ends_with($word, 'ses')) {
                $word = substr($word, 0, -2);
            } elseif (strlen($word) > 3 && str_ends_with($word, 's') && ! str_ends_with($word, 'ss')) {
                $word = substr($word, 0, -1);
            }
            $words[] = $word;
        }

        return $words;
    }
}
