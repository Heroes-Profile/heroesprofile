<?php

namespace App\Support;

/**
 * Match length: the in-game clock the site shows, and seconds for the public API.
 *
 * A replay's stored length runs past the in-game clock: the wait before the
 * gates open plus about 18 seconds after the core dies. ARAM opens its gates at
 * 75 seconds instead of 38, so it runs longer.
 *
 * The site's match controller formats length for display — `12 minutes 7
 * seconds` — after subtracting the pre-game period. Every other public endpoint
 * reports length as a number of seconds, so the same field name meant two
 * different things depending on which one you called.
 *
 * Parsed from the display string rather than recomputed from the raw column: the
 * subtraction is the site's rule, and repeating it here would be a second copy
 * free to drift from the first.
 */
class GameLength
{
    public const OFFSET = 56;

    public const ARAM_OFFSET = 93;

    private const ARAM = 6;

    public static function offset(mixed $gameType): int
    {
        return (int) $gameType === self::ARAM ? self::ARAM_OFFSET : self::OFFSET;
    }

    /** The in-game length of a stored replay length. Esports games have no game type: pass null. */
    public static function played(mixed $storedLength, mixed $gameType): int
    {
        return max(0, (int) $storedLength - self::offset($gameType));
    }

    /** The same offset in SQL, from a game type column. */
    public static function offsetSql(string $gameTypeColumn): string
    {
        return 'CASE WHEN '.$gameTypeColumn.' = '.self::ARAM.' THEN '.self::ARAM_OFFSET.' ELSE '.self::OFFSET.' END';
    }

    /** In-game length in SQL, from the stored length and game type columns. */
    public static function playedSql(string $lengthColumn, string $gameTypeColumn): string
    {
        return 'GREATEST(CAST('.$lengthColumn.' AS SIGNED) - '.self::offsetSql($gameTypeColumn).', 0)';
    }

    /** Left untouched if it is not a string this recognises. */
    public static function seconds(mixed $length): mixed
    {
        if (! is_string($length)) {
            return $length;
        }

        if (! preg_match('/(-?\d+)\s*minutes?\s+(-?\d+)\s*seconds?/i', $length, $parts)) {
            return $length;
        }

        return ((int) $parts[1] * 60) + (int) $parts[2];
    }

    /**
     * The same, applied to a payload's `game_length` wherever it sits.
     *
     * @param  mixed  $payload  an array or anything else, returned as given
     */
    public static function inPayload(mixed $payload): mixed
    {
        if (! is_array($payload)) {
            return $payload;
        }

        foreach ($payload as $key => $value) {
            if ($key === 'game_length') {
                $payload[$key] = self::seconds($value);

                continue;
            }

            if (is_array($value)) {
                $payload[$key] = self::inPayload($value);
            }
        }

        return $payload;
    }
}
