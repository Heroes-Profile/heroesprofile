<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * What an account is told when it is warned, suspended or closed. Written in the
 * console's rich text editor and stored as HTML.
 *
 * Rows written before the editor are plain text, so everything that reads one goes
 * through html() rather than trusting it as markup.
 */
class StandingText
{
    private const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'a', 'ul', 'ol', 'li'];

    private const EMAIL_STYLES = [
        'p' => 'margin:0 0 16px;',
        'ul' => 'margin:0 0 16px;padding-left:22px;',
        'ol' => 'margin:0 0 16px;padding-left:22px;',
        'li' => 'margin:0 0 10px;',
        'a' => 'color:#008b8b;',
    ];

    /** Applied on the way in, so what is stored is already safe to render. */
    public static function clean(string $html): string
    {
        $config = (new HtmlSanitizerConfig)
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->forceAttribute('a', 'target', '_blank');

        foreach (self::TAGS as $tag) {
            $config = $config->allowElement($tag, $tag === 'a' ? ['href'] : []);
        }

        return trim((new HtmlSanitizer($config))->sanitize($html));
    }

    /** Safe markup for a stored reason, old plain-text rows included. */
    public static function html(?string $stored): string
    {
        $stored = trim((string) $stored);

        if ($stored === '') {
            return '';
        }

        if ($stored === strip_tags($stored)) {
            return '<p>'.nl2br(e($stored), false).'</p>';
        }

        return self::clean($stored);
    }

    /**
     * For the API's 403 body, which is JSON read by a program and not rendered.
     * List items and paragraphs become sentences on one line.
     */
    public static function plain(?string $stored): string
    {
        $text = preg_replace('/<\/(p|li)>|<br\s*\/?>/i', ' ', (string) $stored);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Gmail drops stylesheets, so the email's spacing and link colour have to be
     * written onto each tag.
     */
    public static function email(?string $stored): string
    {
        return preg_replace_callback(
            '/<(p|ul|ol|li|a)(?=[\s>])/i',
            fn (array $match) => '<'.$match[1].' style="'.self::EMAIL_STYLES[strtolower($match[1])].'"',
            self::html($stored)
        );
    }
}
