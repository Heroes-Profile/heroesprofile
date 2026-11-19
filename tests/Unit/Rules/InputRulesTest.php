<?php

namespace Tests\Unit\Rules;

use App\Rules\BattletagInputProhibitCharacters;
use App\Rules\DateInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\NgsReplayUrlValidation;
use App\Rules\PartyCombinationRule;
use App\Rules\RegionInputValidation;
use App\Rules\SelectedTalentInputValidation;
use App\Rules\SlRankInputValidation;
use App\Rules\StackSizeInputValidation;
use App\Rules\StatFilterInputValidation;
use App\Rules\TalentBuildTypeInputValidation;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Rules that don't read the database. */
class InputRulesTest extends TestCase
{
    public static function cases(): array
    {
        return [
            'battletag plain' => [new BattletagInputProhibitCharacters, 'Zemill', true],
            'battletag with number' => [new BattletagInputProhibitCharacters, 'Zemill#1940', true],
            'battletag unicode' => [new BattletagInputProhibitCharacters, 'Ärger#123', true],
            'battletag sql' => [new BattletagInputProhibitCharacters, "Zem' OR 1=1", false],
            'battletag two hashes' => [new BattletagInputProhibitCharacters, 'a#1#2', false],
            'battletag array' => [new BattletagInputProhibitCharacters, ['a'], false],

            'date valid' => [new DateInputValidation, '2024-02-29', true],
            'date impossible' => [new DateInputValidation, '2023-02-29', false],
            'date wrong format' => [new DateInputValidation, '02/01/2024', false],

            'hero level band' => [new HeroLevelInputValidation, '25', true],
            'hero level list with one good' => [new HeroLevelInputValidation, '26,40', true],
            'hero level none good' => [new HeroLevelInputValidation, '26', false],

            'party combo' => [new PartyCombinationRule, '00023', true],
            'party combo bad' => [new PartyCombinationRule, '00014', false],

            'region list' => [new RegionInputValidation, 'NA,EU', true],
            'region array' => [new RegionInputValidation, ['KR', 'CN'], true],
            'region one bad' => [new RegionInputValidation, 'NA,SEA', false],

            'selected talents empty' => [new SelectedTalentInputValidation, [], true],
            'selected talents numbers and nulls' => [new SelectedTalentInputValidation, ['1' => 101, '4' => null], true],
            'selected talents text' => [new SelectedTalentInputValidation, ['1' => 'abc'], false],

            'sl rank' => [new SlRankInputValidation, 'gold,master', true],
            'sl rank bad' => [new SlRankInputValidation, 'gold,wood', false],

            'stack size' => [new StackSizeInputValidation, ['Solo', '5 Players'], true],
            'stack size bad' => [new StackSizeInputValidation, '6 Players', false],

            'build type' => [new TalentBuildTypeInputValidation, 'HP Algorithm', true],
            'build type bad' => [new TalentBuildTypeInputValidation, 'Unique Lvl 2', false],

            'stat filter null' => [new StatFilterInputValidation('minor', ['2.55.4.91418']), null, true],
            'stat filter kills minor' => [new StatFilterInputValidation('minor', ['2.55.4.91418']), 'kills', true],
            'stat filter kills major' => [new StatFilterInputValidation('major', ['2.55']), 'kills', false],
            'stat filter win rate major' => [new StatFilterInputValidation('major', ['2.55']), 'win_rate', true],
            'stat filter too many patches' => [new StatFilterInputValidation('minor', 'a,b,c,d,e,f'), 'kills', false],
            'stat filter unknown' => [new StatFilterInputValidation('minor', ['2.55.4.91418']), 'nope', false],
        ];
    }

    #[DataProvider('cases')]
    public function test_rule(object $rule, mixed $value, bool $expected): void
    {
        $this->assertSame($expected, $rule->passes('field', $value));
    }

    public static function ngsUrls(): array
    {
        return [
            'virtual host' => ['https://ngs-replay-storage.s3.amazonaws.com/season/game.StormReplay', true],
            'path style' => ['https://s3.amazonaws.com/ngs-replay-storage/season/game.StormReplay', true],
            'path style other bucket' => ['https://s3.amazonaws.com/other-bucket/game.StormReplay', false],
            'path style bucket only' => ['https://s3.amazonaws.com/ngs-replay-storage/', false],
            'http' => ['http://ngs-replay-storage.s3.amazonaws.com/game.StormReplay', false],
            'other host' => ['https://evil.example.com/game.StormReplay', false],
            'dot segments' => ['https://s3.amazonaws.com/ngs-replay-storage/../other/game', false],
            'encoded dot segments' => ['https://s3.amazonaws.com/ngs-replay-storage/%2e%2e/other/game', false],
            'not a string' => [123, false],
        ];
    }

    #[DataProvider('ngsUrls')]
    public function test_ngs_replay_url(mixed $url, bool $expected): void
    {
        config(['api.ngs.replay_bucket' => 'ngs-replay-storage']);

        $this->assertSame($expected, (new NgsReplayUrlValidation)->passes('url', $url));
    }
}
