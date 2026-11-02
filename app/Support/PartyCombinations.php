<?php

namespace App\Support;

/**
 * The party-composition codes `/party` returns in `ally_combo` and `enemy_combo`.
 *
 * Five digits, one per group size, read left to right as 5-stack, 4-stack, triple,
 * double, solo. Each digit is the number of **players** in groups of that size, not
 * the number of groups — so `00023` is two players in a duo plus three solos, and
 * every code sums to five.
 *
 *     0 0 0 2 3
 *     │ │ │ │ └── 3 players playing solo
 *     │ │ │ └──── 2 players in a duo        → "1 Double, 3 Solo"
 *     │ │ └────── none in a triple
 *     │ └──────── none in a quad
 *     └────────── none in a full five-stack
 *
 * Names here match what `stack_size_name` has always returned, so this is a single
 * definition of existing behaviour rather than a change to it. Note the site's own
 * filter dropdown (`GlobalDataService::$filterData->party_combinations`) words the
 * same codes differently — see the class docblock note in that method.
 */
class PartyCombinations
{
    /** @var array<string, string> */
    public const NAMES = [
        '00005' => '5 Solo',
        '00023' => '1 Double, 3 Solo',
        '00041' => '2 Double, 1 Solo',
        '00302' => '1 Triple, 2 Solo',
        '00320' => '1 Triple, 1 Double',
        '04001' => '1 Quad, 1 Solo',
        '50000' => '1 team of 5',
    ];

    /** The section each ally code is listed under on the party pages. */
    public const SECTIONS = [
        '00005' => 'solo',
        '00023' => 'double',
        '00041' => 'double_double',
        '00302' => 'triple',
        '00320' => 'triple_double',
        '04001' => 'quadruple',
        '50000' => 'quintuple',
    ];

    /** Unrecognised codes read as five solos, which is what the controller did. */
    public const FALLBACK = '5 Solo';

    public static function name(?string $code): string
    {
        return self::NAMES[$code] ?? self::FALLBACK;
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        return self::NAMES;
    }
}
