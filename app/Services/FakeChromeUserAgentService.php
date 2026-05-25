<?php

namespace App\Services;

class FakeChromeUserAgentService
{
    /**
     * Detect common fake Chromium user agents used by replay scrapers.
     */
    public static function isFake(string $userAgent): bool
    {
        if ($userAgent === '') {
            return false;
        }

        if (preg_match('/Edg\/|OPR\/|Brave\/|Vivaldi\/|CriOS\//i', $userAgent)) {
            return false;
        }

        if (preg_match('/Chrome\/\d+\.0\.0\.0(?:\s|;|\/|$)/i', $userAgent)) {
            return true;
        }

        if (preg_match('/Safari\/537\.3(?!6)/i', $userAgent)) {
            return true;
        }

        return false;
    }
}
