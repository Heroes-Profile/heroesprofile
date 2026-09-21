<?php

namespace Tests\Unit\Twitch;

use App\Services\Twitch\TwitchPayload;
use PHPUnit\Framework\TestCase;

class TwitchPayloadTest extends TestCase
{
    private const LIMIT = 5120;

    public function test_a_full_lobby_with_stats_fits_in_one_twitch_message(): void
    {
        $json = TwitchPayload::encode('3f2b8c1e-8a7d-4c52-9d1e-0b6f5a4c3e21', 42, 'in_game', 'Storm League', 'Braxis Holdout', $this->lobby(), self::LIMIT);

        $this->assertLessThanOrEqual(self::LIMIT, strlen($json));

        $decoded = json_decode($json, true);
        $this->assertSame(TwitchPayload::VERSION, $decoded['v']);
        $this->assertCount(10, $decoded['pl']);
        $this->assertNotNull($decoded['pl'][0][6], 'stats should survive at normal size');
    }

    public function test_talents_are_padded_to_seven_tiers_with_zero_for_unpicked(): void
    {
        $players = $this->lobby();
        $players[0]['talents'] = [101, 102];

        $decoded = json_decode(TwitchPayload::encode('g', 1, 'in_game', null, null, $players, self::LIMIT), true);

        $this->assertSame([101, 102, 0, 0, 0, 0, 0], $decoded['pl'][0][5]);
    }

    public function test_stats_are_dropped_before_heroes_or_talents_when_over_the_limit(): void
    {
        $json = TwitchPayload::encode('g', 1, 'in_game', null, null, $this->lobby(), 900);
        $decoded = json_decode($json, true);

        foreach ($decoded['pl'] as $player) {
            $this->assertNotNull($player[4], 'hero kept');
            $this->assertCount(7, $player[5], 'talents kept');
        }

        $this->assertNull($decoded['pl'][0][6], 'stats dropped last resort');
    }

    public function test_compact_stats_omit_modes_the_player_has_never_played(): void
    {
        $stats = TwitchPayload::stats([
            'account_level' => 812,
            'qm_mmr' => 2710, 'qm_games_played' => 900, 'qm_win_rate' => 53.456,
            'sl_mmr' => null, 'sl_games_played' => 0, 'sl_win_rate' => null,
            'ar_mmr' => null, 'ar_games_played' => 12, 'ar_win_rate' => 50.0,
        ], ['qm_rank' => 'Diamond 2', 'sl_rank' => null, 'ar_rank' => null]);

        $this->assertSame(812, $stats['l']);
        $this->assertSame([2710, 'Diamond 2', 53.5, 900], $stats['q']);
        $this->assertArrayNotHasKey('s', $stats);
        $this->assertSame([null, null, 50.0, 12], $stats['a']);
    }

    public function test_inactive_message_is_tiny(): void
    {
        $this->assertSame(['v' => TwitchPayload::VERSION, 'inactive' => true], json_decode(TwitchPayload::inactive(), true));
    }

    /** @return array<int, array<string, mixed>> */
    private function lobby(): array
    {
        $players = [];

        for ($i = 0; $i < 10; $i++) {
            $players[] = [
                'team' => $i < 5 ? 0 : 1,
                'name' => 'LongPlayerName'.$i,
                'blizz_id' => 12345678 + $i,
                'region' => 1,
                'hero_id' => 40 + $i,
                'talents' => [1001, 1002, 1003, 1004, 1005, 1006, 1007],
                'stats' => [
                    'l' => 1234,
                    'q' => [2850, 'Master', 55.2, 2500],
                    's' => [2600, 'Diamond 1', 51.9, 1300],
                    'a' => [2400, 'Platinum 3', 49.8, 800],
                ],
            ];
        }

        return $players;
    }
}
