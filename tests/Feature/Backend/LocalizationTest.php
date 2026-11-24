<?php

namespace Tests\Feature\Backend;

use App\Models\BattlenetAccount;
use App\Models\Hero;
use App\Models\HeroesDataAbility;
use App\Models\HeroTranslation;
use App\Services\GlobalDataService;
use App\Services\LocalizedFaq;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Characterization\SeedFixtures;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeAnthropic;

/** The site and the public API in another language: the locale choice, game strings, filters, FAQ and help chat. */
class LocalizationTest extends FeatureTestCase
{
    use SeedFixtures;

    private const MAP_ID = 4; // Cursed Hollow

    private ?FakeAnthropic $anthropic = null;

    protected function tearDown(): void
    {
        FakeAnthropic::uninstall();

        parent::tearDown();
    }

    private function abathurId(): int
    {
        return (int) Hero::where('name', self::HERO)->value('id');
    }

    /** German rows for Abathur, his level 1 talent, one of his abilities and Cursed Hollow. */
    private function seedGerman(): void
    {
        $db = DB::connection('heroesprofile_translations');
        $db->table('heroes')->insert(['hero_id' => $this->abathurId(), 'locale' => 'de', 'name' => 'Abathur-DE', 'title' => 'Evolutionsmeister']);
        $db->table('maps')->insert(['map_id' => self::MAP_ID, 'locale' => 'de', 'name' => 'Verfluchtes Tal']);
        $db->table('talents')->insert(['talent_id' => self::HERO_LEVEL_ONE_TALENT, 'locale' => 'de', 'name' => 'Vergiftetes Nest', 'description' => 'Giftnester verursachen mehr Schaden.']);

        // The seeded abilities have no link_id, which is what translations match on
        HeroesDataAbility::where('hero_name', self::HERO)->where('title', 'Carapace')->update(['link_id' => 'AbathurCarapace']);
        $db->table('abilities')->insert(['hero_name' => self::HERO, 'link_id' => 'AbathurCarapace', 'locale' => 'de', 'name' => 'Panzer-DE']);
    }

    private function signIn(): BattlenetAccount
    {
        $user = BattlenetAccount::forceCreate([
            'battlenet_accounts_id' => 900060, 'battlenet_id' => 900060, 'battletag' => self::PLAYER_BATTLETAG.'#194',
            'blizz_id' => self::PLAYER_BLIZZ_ID, 'region' => self::PLAYER_REGION,
        ]);
        $this->actingAs($user);

        return $user;
    }

    /** Test cookies stick for the rest of the test; JSON requests only send them with credentials. */
    private function inLocale(string $locale): static
    {
        return $this->withCredentials()->withUnencryptedCookie('locale', $locale);
    }

    private function inGerman(): static
    {
        return $this->inLocale('de');
    }

    /** The first array anywhere in $data whose talent_id is $talentId. */
    private function findTalent(mixed $data, int $talentId): ?array
    {
        if (! is_array($data)) {
            return null;
        }
        if ((int) ($data['talent_id'] ?? 0) === $talentId && isset($data['title'])) {
            return $data;
        }
        foreach ($data as $child) {
            if ($found = $this->findTalent($child, $talentId)) {
                return $found;
            }
        }

        return null;
    }

    private function globalTalents()
    {
        return $this->postJson('/api/v1/global/talents', [
            'hero' => self::HERO, 'timeframe_type' => 'minor', 'timeframe' => [self::GAME_VERSION], 'game_type' => ['sl'],
            'region' => null, 'statfilter' => 'win_rate', 'hero_level' => null, 'role' => null, 'game_map' => null,
            'league_tier' => null, 'hero_league_tier' => null, 'role_league_tier' => null, 'mirror' => 0,
        ])->assertOk();
    }

    // Choosing the language

    public function test_the_locale_comes_from_the_account_setting_then_the_cookie(): void
    {
        $locale = fn () => app(GlobalDataService::class)->getLocale();

        $this->assertSame('en', $locale());

        request()->cookies->set('locale', 'fr');
        $this->assertSame('fr', $locale());

        request()->cookies->set('locale', 'xx');
        $this->assertSame('en', $locale(), 'an unsupported cookie is English');

        request()->cookies->set('locale', 'fr');
        $user = $this->signIn();
        $user->userSettings()->create(['setting' => 'locale', 'value' => 'de']);
        $this->assertSame('de', $locale(), 'the account setting beats the cookie');
    }

    public function test_a_signed_in_account_saves_its_language_and_bad_values_are_refused(): void
    {
        $user = $this->signIn();

        $this->postJson('/api/v1/profile/save/settings', ['locale' => 'pt-BR'])->assertOk();
        $this->assertSame('pt-BR', $user->userSettings()->where('setting', 'locale')->value('value'));

        $this->postJson('/api/v1/profile/save/settings', ['locale' => 'klingon'])->assertJson(['status' => 'failure to validate inputs']);
        $this->assertSame('pt-BR', $user->userSettings()->where('setting', 'locale')->value('value'));
    }

    // Game strings on the site

    public function test_site_json_gets_localized_talent_text_and_english_stays_as_it_was(): void
    {
        $this->seedGerman();

        $english = $this->findTalent($this->globalTalents()->json(), self::HERO_LEVEL_ONE_TALENT);
        $this->assertNotNull($english);
        $this->assertArrayNotHasKey('localized_name', $english);

        $german = $this->findTalent($this->inGerman()->globalTalents()->json(), self::HERO_LEVEL_ONE_TALENT);
        $this->assertSame('Vergiftetes Nest', $german['localized_name']);
        $this->assertSame('Giftnester verursachen mehr Schaden.', $german['localized_description']);
        $this->assertSame($english['title'], $german['title'], 'the English title is kept for matching');
    }

    public function test_a_talent_without_a_translation_is_left_in_english(): void
    {
        // German rows exist, but none for this talent
        DB::connection('heroesprofile_translations')->table('talents')
            ->insert(['talent_id' => 999999, 'locale' => 'de', 'name' => 'Nirgendwo']);

        $talent = $this->findTalent($this->inGerman()->globalTalents()->json(), self::HERO_LEVEL_ONE_TALENT);

        $this->assertArrayNotHasKey('localized_name', $talent);
    }

    public function test_the_hero_page_carries_the_heros_talents_abilities_and_name_in_the_chosen_language(): void
    {
        $this->seedGerman();

        $herodata = $this->inGerman()->get('/Heroes/'.self::HERO)->assertOk()->viewData('herodata');

        $this->assertSame('Abathur-DE', $herodata['localized']['name']);
        $this->assertSame('Evolutionsmeister', $herodata['localized']['title']);
        $this->assertSame('Vergiftetes Nest', $this->findTalent($herodata['talents'], self::HERO_LEVEL_ONE_TALENT)['localized_name']);
        $this->assertContains('Panzer-DE', array_column($herodata['abilities'], 'localized_name'));

        $english = $this->inLocale('en')->get('/Heroes/'.self::HERO)->assertOk()->viewData('herodata');
        $this->assertArrayNotHasKey('localized', $english);
    }

    public function test_filter_options_get_a_label_in_the_chosen_language_and_keep_their_english_name(): void
    {
        $this->seedGerman();
        $filters = fn () => app(GlobalDataService::class)->getFilterData();

        $english = $filters();
        $this->assertArrayNotHasKey('label', collect($english->heroes)->firstWhere('name', self::HERO));

        App::setLocale('de');
        $german = $filters();

        $abathur = collect($german->heroes)->firstWhere('name', self::HERO);
        $this->assertSame('Abathur-DE', $abathur['label']);
        $this->assertSame('Verfluchtes Tal', collect($german->game_maps)->firstWhere('name', self::MAP)['label']);

        // A hero with no German row falls back to its English name
        $other = collect($german->heroes)->firstWhere('name', '!=', self::HERO);
        $this->assertSame($other['name'], $other['label']);

        $season = collect($german->seasons)->first(fn ($s) => str_contains($s['name'], 'Season'));
        $this->assertStringContainsString('Saison', $season['label']);
        $this->assertStringContainsString('Season', $season['name']);
    }

    public function test_a_localized_hero_name_resolves_to_the_hero(): void
    {
        $this->seedGerman();

        $this->assertSame($this->abathurId(), HeroTranslation::heroIdFor('Abathur-DE'));
        $this->assertSame($this->abathurId(), HeroTranslation::heroIdFor('abathur-de'), 'case-insensitive');
        $this->assertNull(HeroTranslation::heroIdFor('Niemand'));

        $names = HeroTranslation::englishNamesByLocalized();
        $this->assertSame(self::HERO, $names['Abathur-DE']);
        $this->assertSame(self::HERO, $names['abathur-de']);
    }

    // FAQ and help chat

    public function test_the_faq_shows_translated_items_and_falls_back_to_english(): void
    {
        $item = config('faq.categories')[0]['items'][0];
        $translated = LocalizedFaq::load('de')['items'][LocalizedFaq::hash($item)] ?? null;
        $this->assertNotNull($translated, 'lang/faq/de.json has the first FAQ item');

        $questions = fn ($response) => collect($response->assertOk()->viewData('faqCategories'))->pluck('items')->flatten(1)->pluck('q');

        $this->assertContains($translated['q'], $questions($this->inGerman()->get('/FAQ')));
        $this->assertContains($item['q'], $questions($this->inLocale('en')->get('/FAQ')));

        // An item edited since its translation shows in English
        $edited = [['name' => 'Test', 'items' => [['q' => $item['q'], 'a' => $item['a'].' Edited.']]]];
        $this->assertSame($item['q'], LocalizedFaq::localize($edited, 'de')[0]['items'][0]['q']);
    }

    private function ask(string $question)
    {
        return $this->postJson('/api/v1/help/chat', ['messages' => [['role' => 'user', 'content' => $question]]]);
    }

    private function fakeClaude(): FakeAnthropic
    {
        config(['services.help_chat.anthropic_api_key' => 'sk-ant-test']);
        Cache::store('database')->flush();

        return $this->anthropic = FakeAnthropic::install()->reply('Antwort.');
    }

    private function systemPrompt(int $request): string
    {
        return collect($this->anthropic->requests[$request]['body']['system'])->pluck('text')->implode("\n");
    }

    public function test_the_help_chat_is_told_to_reply_in_the_chosen_language(): void
    {
        $this->fakeClaude();
        $this->signIn();

        $this->ask('How do I upload replays?')->assertOk();
        $this->assertStringNotContainsString('Reply in', $this->systemPrompt(0));

        $this->inGerman()->ask('Wie lade ich Replays hoch?')->assertOk();
        $this->assertStringContainsString('Reply in Deutsch', $this->systemPrompt(1));
    }

    public function test_help_chat_questions_in_other_scripts_get_their_own_cached_answers(): void
    {
        $this->fakeClaude();
        $this->signIn();

        $this->ask('Как загрузить реплеи?')->assertOk();
        $this->ask('Что такое MMR?')->assertOk();
        $this->assertCount(2, $this->anthropic->requests, 'different Russian questions are not one cache key');

        $this->ask('Как загрузить реплеи?')->assertOk();
        $this->assertCount(2, $this->anthropic->requests, 'the same question is served from the cache');
    }

    // Public API ?lang=

    private static function key(): string
    {
        return str_pad('basic_local_api_key_', 60, '0');
    }

    public function test_api_lang_adds_localized_names_next_to_the_english_ones(): void
    {
        $this->seedGerman();

        $maps = $this->getJson('/api/external/v1/maps?lang=DE&api_token='.self::key())->assertOk()->assertHeader('Content-Language', 'de');
        $cursed = collect($maps->json('maps'))->firstWhere('name', self::MAP);
        $this->assertSame('Verfluchtes Tal', $cursed['name_localized']);

        $heroes = collect($this->getJson('/api/external/v1/heroes?lang=de&api_token='.self::key())->assertOk()->json('heroes'));
        $this->assertSame('Abathur-DE', $heroes->firstWhere('name', self::HERO)['name_localized']);
        $this->assertSame('Heiler', $heroes->firstWhere('new_role', 'Healer')['new_role_localized'], 'role names come from lang/de.json');

        // English, or a language we don't have, adds nothing
        foreach (['', '&lang=en', '&lang=xx'] as $lang) {
            $response = $this->getJson('/api/external/v1/maps?api_token='.self::key().$lang)->assertOk()->assertHeaderMissing('Content-Language');
            $this->assertArrayNotHasKey('name_localized', collect($response->json('maps'))->firstWhere('name', self::MAP));
        }
    }

    public function test_api_lang_adds_the_localized_columns_to_csv_too(): void
    {
        $this->seedGerman();

        $csv = $this->get('/api/external/v1/maps?mode=csv&lang=de&api_token='.self::key())->assertOk()->streamedContent();

        $this->assertStringContainsString('name_localized', strtok($csv, "\n"));
        $this->assertStringContainsString('Verfluchtes Tal', $csv);
    }

    public function test_api_errors_are_in_the_requested_language(): void
    {
        $this->getJson('/api/external/v1/maps?lang=de')->assertUnauthorized()
            ->assertJsonPath('error.message', 'Ein gültiger API-Schlüssel ist erforderlich.');

        $this->getJson('/api/external/v1/maps')->assertUnauthorized()
            ->assertJsonPath('error.message', 'A valid API key is required.');
    }
}
