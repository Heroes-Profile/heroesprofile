<?php

namespace Tests\Unit\Support;

use App\Support\HeroLevelBands;
use App\Support\PartyCombinations;
use App\Support\StandingText;
use PHPUnit\Framework\TestCase;

class LookupTablesTest extends TestCase
{
    public function test_hero_level_bands(): void
    {
        $this->assertSame([1, 5, 10, 15, 25, 40, 60, 80, 100], array_keys(HeroLevelBands::all()));
        $this->assertTrue(HeroLevelBands::isValid('25'));
        $this->assertFalse(HeroLevelBands::isValid('26'));
        $this->assertFalse(HeroLevelBands::isValid(null));
    }

    public function test_party_combination_names_and_fallback(): void
    {
        $this->assertSame('1 Double, 3 Solo', PartyCombinations::name('00023'));
        $this->assertSame('1 team of 5', PartyCombinations::name('50000'));
        $this->assertSame('5 Solo', PartyCombinations::name('99999'));
        $this->assertSame('5 Solo', PartyCombinations::name(null));
        $this->assertSame(array_keys(PartyCombinations::NAMES), array_keys(PartyCombinations::SECTIONS));

        foreach (array_keys(PartyCombinations::all()) as $code) {
            $this->assertSame(5, array_sum(str_split((string) $code)), "code {$code} should sum to five players");
        }
    }

    public function test_standing_text_plain_rows_are_escaped(): void
    {
        $this->assertSame('', StandingText::html('  '));
        $this->assertSame("<p>a &amp; b<br>\nc</p>", StandingText::html("a & b\nc"));
        // Anything tag-like goes through the sanitizer instead
        $this->assertSame('a', StandingText::html('a <b'));
    }

    public function test_standing_text_strips_disallowed_markup(): void
    {
        $clean = StandingText::clean('<p onclick="x()">Hi <script>alert(1)</script><a href="javascript:x">l</a></p>');

        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript', $clean);
    }

    public function test_standing_text_plain_and_email(): void
    {
        $this->assertSame('One. Two &', StandingText::plain('<p>One.</p><ul><li>Two &amp;</li></ul>'));
        $this->assertSame('<p style="margin:0 0 16px;">x</p>', StandingText::email('<p>x</p>'));
    }
}
