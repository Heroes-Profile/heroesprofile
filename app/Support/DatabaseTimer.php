<?php

namespace App\Support;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;

/**
 * Running total of time spent in database queries, across every connection.
 *
 * Read as laps: take a mark, do the work, ask how much has passed since. One
 * listener for the process, so marks can nest without double counting.
 */
class DatabaseTimer
{
    private static bool $listening = false;

    private static float $totalMs = 0.0;

    public static function mark(): float
    {
        if (! self::$listening) {
            Event::listen(QueryExecuted::class, function (QueryExecuted $event) {
                self::$totalMs += (float) $event->time;
            });

            self::$listening = true;
        }

        return self::$totalMs;
    }

    public static function since(float $mark): int
    {
        return (int) round(self::$totalMs - $mark);
    }
}
