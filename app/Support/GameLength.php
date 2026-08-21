<?php

namespace App\Support;

/**
 * Match length as seconds, for the public API.
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
