<?php

namespace App\Http\Controllers;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Models\HelpChatMiss;
use App\Services\FaqMatcher;
use App\Services\GlobalDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class HelpChatController extends Controller
{
    private const MODEL = 'claude-haiku-5-5';

    private const DAILY_LIMIT = 20;

    // Extra questions a day for Patreon supporters
    private const PATREON_BONUS = 5;

    private const ANSWER_CACHE_DAYS = 7;

    // Earlier turns sent back with each question
    private const HISTORY_TURNS = 10;

    // Every question starts here, for anyone: the FAQ and site pages that fit it. Free; Claude only runs from ask().
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => __('Questions can be up to 500 characters.')], 422);
        }

        return response()->json(app(FaqMatcher::class)->search($request['question']) + [
            // Profile links are Zemill's examples until they log in
            'examples' => FaqMatcher::viewerProfile() === null,
            'persona' => $this->persona($request['question']),
        ]);
    }

    // Rude messages get Xal'atath, asking for a manager gets Diablo; rude wins when both. The widget keeps it for the rest of the chat.
    private function persona(string $question): ?array
    {
        $text = mb_strtolower($question);

        // Rude means rude to Heroes Profile: insults at whoever's reading, or harsh words about the site, bot or "you".
        // "Why does Li-Ming suck this patch" is about the game, not us.
        $insult = preg_match('/\b(idiots?|moron|stfu|shut up|(fuck|screw|f) (you|u|off)|bite me)\b/', $text);
        $harsh = preg_match('/\b(stupid|dumb|useless|trash|garbage|sucks?|suck(ed|ing)|terrible|awful|worst|hate|crap|fuck\w*|shit\w*|bitch\w*|ass(hole)?s?|damn|wtf)\b/', $text);
        $aimedAtUs = preg_match('/\b(you|your|you\'re|youre|u|ur|site|website|heroes ?profile|bot|assistant|app|chat)\b/', $text);

        if ($insult || ($harsh && $aimedAtUs)) {
            return [
                'name' => 'xalatath',
                'logo' => '/images/event/xalatath/xalatath-logo-stage-5.png',
                'line' => __('Such hostility. The void remembers every word.'),
            ];
        }

        if (preg_match('/\b(managers?|supervisor|real person|actual person|a human|speak to someone|talk to someone|customer service)\b/', $text)) {
            return [
                'name' => 'diablo',
                'logo' => '/images/logo/heroesprofilelogo_dp.png',
                'line' => __('You asked for the manager. The Lord of Terror is listening.'),
            ];
        }

        return null;
    }

    // "Ask the assistant", after the search results
    public function ask(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => __('Log in with Battle.net to use the help chat.')], 401);
        }

        $validator = Validator::make($request->all(), [
            'messages' => 'required|array|min:1|max:40',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:2000',
            // The top search result they saw first, if any
            'faq_question' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => __('Invalid message.')], 422);
        }

        $messages = collect($request['messages'])
            ->map(fn ($message) => ['role' => $message['role'], 'content' => $message['content']])
            ->values();

        if ($messages->last()['role'] !== 'user' || mb_strlen($messages->last()['content']) > 500) {
            return response()->json(['error' => __('Questions can be up to 500 characters.')], 422);
        }

        $user = Auth::user();
        $key = 'help-chat:'.($user->battlenet_accounts_id ?? $user->blizz_id.'-'.$user->region);
        $limit = $this->dailyLimit($user);
        $question = $messages->last()['content'];

        // Recent turns only, starting on a question
        $history = $messages->slice(-self::HISTORY_TURNS)->values();
        while ($history->isNotEmpty() && $history->first()['role'] !== 'user') {
            $history = $history->slice(1)->values();
        }

        $systemPrompt = $this->systemPrompt();

        // Opening questions asked word-for-word before reuse that answer, for free. Later questions always go to Claude
        // with the chat so far, so context is never lost. A new FAQ or page list gives new keys.
        $cacheKey = $history->count() === 1 ? $this->answerCacheKey($question, $systemPrompt) : null;
        if ($cacheKey && ($cached = Cache::store('database')->get($cacheKey))) {
            $cached = $this->personalize($cached);
            $this->storeMiss($request, $question, $cached);

            return response()->json([
                'answer' => $cached,
                'remaining' => RateLimiter::remaining($key, $limit),
            ]);
        }

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json([
                'error' => __("You have used today's :limit questions. Try again tomorrow, or search the FAQ.", ['limit' => $limit]),
                'remaining' => 0,
            ], 429);
        }

        // Without it the SDK would fall back to ANTHROPIC_API_KEY or a signed-in profile, outside the Help Chat workspace
        $apiKey = config('services.help_chat.anthropic_api_key');
        if (! $apiKey) {
            Log::warning('Help chat has no HEROESPROFILE_CHAT_ANTHROPIC_API_KEY');

            return response()->json(['error' => __('The help chat is unavailable right now. Try the FAQ instead.')], 503);
        }

        try {
            $response = (new Client(apiKey: $apiKey))->messages->create(
                model: self::MODEL,
                maxTokens: 1024,
                system: [
                    ['type' => 'text', 'text' => $systemPrompt, 'cacheControl' => ['type' => 'ephemeral']],
                ],
                messages: $history->all(),
                outputConfig: ['effort' => 'low'],
            );
        } catch (RateLimitException $e) {
            return response()->json(['error' => __('The help chat is busy. Try again in a minute.')], 503);
        } catch (APIStatusException $e) {
            Log::warning('Help chat request failed', ['type' => $e->type?->value, 'message' => $e->getMessage()]);

            return response()->json(['error' => __('The help chat is unavailable right now. Try the FAQ instead.')], 503);
        } catch (\Throwable $e) {
            Log::warning('Help chat request failed', ['message' => $e->getMessage()]);

            return response()->json(['error' => __('The help chat is unavailable right now. Try the FAQ instead.')], 503);
        }

        RateLimiter::hit($key, 86400);

        if ($response->stopReason === 'refusal') {
            $answer = __('Sorry, I can only help with questions about Heroes Profile. Try the FAQ at /FAQ or contact us at /Contact.');
        } else {
            $answer = trim(collect($response->content)
                ->filter(fn ($block) => $block->type === 'text')
                ->map(fn ($block) => $block->text)
                ->implode("\n\n"));

            if ($cacheKey && $answer !== '') {
                Cache::store('database')->put($cacheKey, $answer, now()->addDays(self::ANSWER_CACHE_DAYS));
            }
        }

        $answer = $this->personalize($answer);
        $this->storeMiss($request, $question, $answer);

        return response()->json([
            'answer' => $answer,
            'remaining' => RateLimiter::remaining($key, $limit),
        ]);
    }

    // Same supporter test as Player Analytics: a linked Patreon account with site flair or ad-free on
    private function dailyLimit($user): int
    {
        $patreon = $user->patreonAccount;
        $supporter = $patreon && ($patreon->site_flair == 1 || $patreon->ad_free == 1);

        return self::DAILY_LIMIT + ($supporter ? self::PATREON_BONUS : 0);
    }

    // Answers are cached with {profile} and {season} so one answer serves everyone; filled in per user here
    private function personalize(string $answer): string
    {
        if (str_contains($answer, '{profile}')) {
            $answer = str_replace('{profile}', FaqMatcher::viewerProfile(), $answer);
        }

        if (str_contains($answer, '{season}')) {
            $answer = str_replace('{season}', (string) app(GlobalDataService::class)->getDefaultSeason(), $answer);
        }

        return $answer;
    }

    // Same question once case, punctuation and spacing are ignored
    private function answerCacheKey(string $question, string $systemPrompt): string
    {
        // Letters and digits in any script, so questions in Chinese or Russian don't all collapse to the same key
        $normalized = trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($question)));

        return 'help_chat_answer|'.self::MODEL.'|'.sha1($systemPrompt).'|'.sha1($normalized);
    }

    // The FAQ didn't answer this one: keep it for improving the FAQ. Cached repeats count too, to show what's asked often.
    private function storeMiss(Request $request, string $question, string $answer): void
    {
        try {
            HelpChatMiss::create([
                'battlenet_accounts_id' => Auth::user()->battlenet_accounts_id,
                'question' => mb_substr($question, 0, 500),
                // asked_anyway: the search showed something and it didn't help
                'reason' => $request['faq_question'] ? 'asked_anyway' : 'no_faq_match',
                'faq_question' => $request['faq_question'],
                'answer' => $answer,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Help chat miss not stored', ['message' => $e->getMessage()]);
        }
    }

    // Built from config/faq.php so the chat and the FAQ page stay in step. Same text every time, so it caches.
    private function systemPrompt(): string
    {
        $faq = collect(config('faq.categories'))->map(function ($category) {
            $items = collect($category['items'])->map(fn ($item) => 'Q: '.$item['q']."\nA: ".$this->plainText($item['a']));

            return '## '.$category['name']."\n\n".$items->implode("\n\n");
        })->implode("\n\n");

        $pages = collect(config('faq.pages'))->map(function ($section) {
            $items = collect($section['items'])->map(fn ($page) => '- '.$page['name'].': '.$page['url'].' : '.$page['description']
                .(isset($page['hero_url']) ? ' For one hero: '.$page['hero_url'] : ''));

            return '## '.$section['name'].(isset($section['note']) ? '
'.$section['note'] : '').'
'.$items->implode('
');
        })->implode('

');

        // English keeps the prompt (and its cached answers) as it was
        $locale = App::getLocale();
        $languageRule = $locale === 'en' ? '' : "\n- Reply in ".config('app.supported_locales')[$locale].', the language they picked on the site, unless they write to you in another language; then reply in theirs. Use hero, map and talent names as the game shows them in that language, but keep every path and URL parameter in English.';

        return <<<PROMPT
            You are the help assistant on Heroes Profile (heroesprofile.com), a statistics site for the game Heroes of the Storm.

            Answer questions about the site: what it offers, where things are, and how its features work. Use only the site guide and FAQ below.
            - Keep answers short: a few sentences, plain text, no markdown headings or tables.
            - When a page helps, give its path starting with "/" (for example /Global/Draft) so it becomes a link.
            - If the guide doesn't cover something, say you're not sure and point to /FAQ or /Contact. Never make up features, numbers or stats.
            - When the question is about one hero and the page has a "For one hero" path, link that path with the hero's name in place of {hero}, written as it appears in the game (spaces as %20, e.g. /Global/Hero?hero=Li%20Li).
            - The person asking is logged in, and you can link their own profile. Player profile paths in the guide are Zemill's examples: for the person asking, write {profile} in place of /Player/Zemill/67280/1, e.g. {profile}/Match/History. Never write {profile} with anything in front of it.
            - Player profile pages take a season. For questions about "this season" or a season count, add ?season={season} for the current season (&season={season} if the path already has a ?), e.g. {profile}?season={season}.
            - Only for someone else's profile, tell them to search that BattleTag in the bar at the top.
            - You can't see stats, matches or numbers yourself. Link the page that shows what they want instead.
            - For questions unrelated to Heroes Profile or Heroes of the Storm, politely say you can only help with Heroes Profile.{$languageRule}

            # Site guide

            {$pages}

            # FAQ

            {$faq}
            PROMPT;
    }

    // FAQ answers are HTML: keep link targets, drop the markup
    private function plainText(string $html): string
    {
        $text = preg_replace('/<a\s[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/s', '$2 ($1)', $html);
        $text = preg_replace('/<li[^>]*>/', "\n- ", $text);
        $text = preg_replace('/<\/?(p|ul|ol|br)[^>]*>/', "\n", $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5);

        return trim(preg_replace("/\n{2,}/", "\n", preg_replace('/[ \t]+/', ' ', $text)));
    }
}
