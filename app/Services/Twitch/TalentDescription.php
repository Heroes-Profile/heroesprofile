<?php

namespace App\Services\Twitch;

/**
 * Talent text for the extension, styled the way the site styles it.
 *
 * The site parses the game's own markup in the browser (resources/js/utils/gameTooltip.js)
 * and the extension cannot: shipping raw markup would put another 240KB in the bundle,
 * which a mobile viewer downloads before anything renders. So the parsing happens here,
 * once, and the bundle carries the result.
 *
 * The shape is kept small because every talent in the game is in that one file:
 *
 *   [ paragraph, paragraph, … ]        a paragraph per <n/><n/>
 *   paragraph = [ run, run, … ]        a run is "plain text" or ["text", code]
 *
 * A single <n/> stays as a newline inside a run's text. Codes are the styles the game
 * asks for; 8 is the quest icon, which carries no text.
 */
class TalentDescription
{
    public const PLAIN = 0;

    public const NUMBER = 1;

    public const GRAY = 2;

    public const QUEST = 3;

    public const GREEN = 4;

    public const VIOLET = 5;

    public const BOLD = 6;

    public const SUBSCRIPT = 7;

    public const QUEST_ICON = 8;

    /** Game colour tags, as gameTooltip.js maps them. */
    private const COLOURS = [
        '#TooltipNumbers' => self::NUMBER,
        '#ColorGray' => self::GRAY,
        '#TooltipQuest' => self::QUEST,
        '#TooltipGreen' => self::GREEN,
        '#AbilityPassive' => self::GREEN,
        '#ColorViolet' => self::VIOLET,
    ];

    private const STYLES = [
        'StandardTooltipHeader' => self::BOLD,
        'TooltipSubscript' => self::SUBSCRIPT,
    ];

    /** Labels the flat text uses when there is no markup to parse. */
    private const LABELS = 'Repeatable Quest|Repeatable Reward|Mythic Quest|Mythic Reward|Quest|Reward|Passive|Gambit';

    /**
     * @return array<int, array<int, string|array{0: string, 1: int}>>
     */
    public static function segments(?string $raw, ?string $flat): array
    {
        $paragraphs = $raw !== null && $raw !== ''
            ? self::fromMarkup($raw)
            : self::fromFlat((string) $flat);

        return array_values(array_filter($paragraphs, fn ($paragraph) => $paragraph !== []));
    }

    /**
     * @return array<int, array<int, string|array{0: string, 1: int}>>
     */
    private static function fromMarkup(string $raw): array
    {
        $runs = self::annotateRates(self::tokenise($raw));
        $paragraphs = [[]];

        foreach ($runs as $run) {
            if (($run['break'] ?? false) === true) {
                $current = &$paragraphs[count($paragraphs) - 1];

                // Two in a row is a new paragraph; one on its own is a line break.
                if ($current !== [] && ($current[count($current) - 1]['pendingBreak'] ?? false)) {
                    array_pop($current);
                    unset($current);
                    $paragraphs[] = [];

                    continue;
                }

                $current[] = ['text' => "\n", 'style' => self::PLAIN, 'pendingBreak' => true];
                unset($current);

                continue;
            }

            $paragraphs[count($paragraphs) - 1][] = $run;
        }

        return array_map(fn ($paragraph) => self::pack($paragraph), $paragraphs);
    }

    /**
     * No markup stored for this one: the flat text still carries paragraphs as double
     * spaces and "Quest:" style labels, which the site pulls apart the same way.
     *
     * @return array<int, array<int, string|array{0: string, 1: int}>>
     */
    private static function fromFlat(string $flat): array
    {
        if (trim($flat) === '') {
            return [];
        }

        $parts = preg_split('/\s{2,}|\s(?<!Mythic )(?<!Repeatable )(?=(?:'.self::LABELS.'):)/u', $flat) ?: [];
        $paragraphs = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            if (preg_match('/^('.self::LABELS.'):\s*(.*)$/us', $part, $match) !== 1) {
                $paragraphs[] = [$part];

                continue;
            }

            [, $label, $text] = $match;
            $quest = $label !== 'Passive' && $label !== 'Gambit';
            $paragraph = [];

            if ($quest) {
                $paragraph[] = ['', self::QUEST_ICON];
            }

            $paragraph[] = [$label.':', $quest ? self::QUEST : self::BOLD];
            $paragraph[] = ' '.$text;
            $paragraphs[] = $paragraph;
        }

        return $paragraphs;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function tokenise(string $raw): array
    {
        $runs = [];
        $stack = [];
        $offset = 0;

        preg_match_all('/<(\/?)(c|s|n|img)\b([^>]*?)\/?>/i', $raw, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        $push = function (string $text) use (&$runs, &$stack) {
            // Muradin's Storm Bolt has a stray "<" before a tag.
            $text = preg_replace('/<$/', '', $text);

            if ($text !== '' && $text !== null) {
                $runs[] = ['text' => $text, 'style' => end($stack) ?: self::PLAIN];
            }
        };

        foreach ($matches as $match) {
            [$whole, $start] = $match[0];
            $push(substr($raw, $offset, $start - $offset));
            $offset = $start + strlen($whole);

            $closing = $match[1][0] !== '';
            $tag = strtolower($match[2][0]);
            $attrs = $match[3][0];
            $value = preg_match('/val="([^"]*)"/i', $attrs, $found) === 1 ? $found[1] : null;

            if ($tag === 'n') {
                $runs[] = ['break' => true];
            } elseif ($tag === 'img') {
                if (stripos($attrs, 'quest') !== false) {
                    $runs[] = ['questIcon' => true];
                }
            } elseif ($closing) {
                array_pop($stack);
            } else {
                $stack[] = $tag === 'c'
                    ? (self::COLOURS[$value] ?? self::PLAIN)
                    : (self::STYLES[$value] ?? self::PLAIN);
            }
        }

        $push(substr($raw, $offset));

        return $runs;
    }

    /**
     * A number is followed by its per-level rate as ~~0.04~~. The site scales the number
     * to a chosen hero level; the extension has no level to scale to, so the base value
     * stays and the rate is spelled out beside it.
     *
     * @param  array<int, array<string, mixed>>  $runs
     * @return array<int, array<string, mixed>>
     */
    private static function annotateRates(array $runs): array
    {
        $result = [];

        foreach ($runs as $run) {
            $text = $run['text'] ?? null;

            if ($text === null || preg_match('/^~~([^~]*)~~$/', $text, $match) !== 1) {
                $result[] = $run;

                continue;
            }

            $previous = $result === [] ? null : $result[count($result) - 1];

            if (! is_numeric($match[1])) {
                // HDP writes 0~~NaN~~ where it could not resolve a value; drop the number too.
                if ($previous !== null && ($previous['style'] ?? null) === self::NUMBER) {
                    array_pop($result);
                }

                continue;
            }

            $rate = round((float) $match[1] * 100, 2);
            $result[] = ['text' => ' (+'.rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.').'% per level)', 'style' => self::GRAY];
        }

        return $result;
    }

    /**
     * Runs into the stored shape: a bare string when it carries no style, trimmed of the
     * breaks the game leaves at the edges of a paragraph.
     *
     * @param  array<int, array<string, mixed>>  $paragraph
     * @return array<int, string|array{0: string, 1: int}>
     */
    private static function pack(array $paragraph): array
    {
        while ($paragraph !== [] && ($paragraph[0]['pendingBreak'] ?? false)) {
            array_shift($paragraph);
        }

        while ($paragraph !== [] && ($paragraph[count($paragraph) - 1]['pendingBreak'] ?? false)) {
            array_pop($paragraph);
        }

        $packed = [];

        foreach ($paragraph as $run) {
            if ($run['questIcon'] ?? false) {
                $packed[] = ['', self::QUEST_ICON];

                continue;
            }

            $text = $run['text'] ?? '';
            $style = $run['style'] ?? self::PLAIN;

            if ($text === '') {
                continue;
            }

            $packed[] = $style === self::PLAIN ? $text : [$text, $style];
        }

        return self::hasContent($packed) ? $packed : [];
    }

    /**
     * @param  array<int, string|array{0: string, 1: int}>  $packed
     */
    private static function hasContent(array $packed): bool
    {
        foreach ($packed as $run) {
            if (is_array($run)) {
                return true;
            }

            if (trim($run) !== '') {
                return true;
            }
        }

        return false;
    }
}
