<?php

namespace Tests\Unit\Support;

use App\Support\GameLength;
use PHPUnit\Framework\TestCase;

class GameLengthTest extends TestCase
{
    public function test_aram_uses_its_own_offset(): void
    {
        $this->assertSame(56, GameLength::offset(1));
        $this->assertSame(56, GameLength::offset(null));
        $this->assertSame(93, GameLength::offset(6));
        $this->assertSame(93, GameLength::offset('6'));
    }

    public function test_played_subtracts_the_offset_and_never_goes_negative(): void
    {
        $this->assertSame(1144, GameLength::played(1200, 5));
        $this->assertSame(1107, GameLength::played('1200', 6));
        $this->assertSame(0, GameLength::played(30, 1));
    }

    public function test_sql_fragments(): void
    {
        $this->assertSame('CASE WHEN r.game_type = 6 THEN 93 ELSE 56 END', GameLength::offsetSql('r.game_type'));
        $this->assertSame('GREATEST(CAST(r.game_length AS SIGNED) - CASE WHEN r.game_type = 6 THEN 93 ELSE 56 END, 0)', GameLength::playedSql('r.game_length', 'r.game_type'));
    }

    public function test_seconds_parses_the_display_string(): void
    {
        $this->assertSame(727, GameLength::seconds('12 minutes 7 seconds'));
        $this->assertSame(61, GameLength::seconds('1 minute 1 second'));
        $this->assertSame('soon', GameLength::seconds('soon'));
        $this->assertSame(300, GameLength::seconds(300));
        $this->assertNull(GameLength::seconds(null));
    }

    public function test_in_payload_converts_every_nested_game_length(): void
    {
        $payload = [
            'game_length' => '20 minutes 0 seconds',
            'games' => [
                ['game_length' => '10 minutes 5 seconds', 'map' => 'Cursed Hollow'],
                ['other' => ['game_length' => '0 minutes 59 seconds']],
            ],
        ];

        $this->assertSame([
            'game_length' => 1200,
            'games' => [
                ['game_length' => 605, 'map' => 'Cursed Hollow'],
                ['other' => ['game_length' => 59]],
            ],
        ], GameLength::inPayload($payload));

        $this->assertSame('x', GameLength::inPayload('x'));
    }
}
