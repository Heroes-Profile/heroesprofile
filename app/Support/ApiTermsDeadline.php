<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * The grace period in `api.terms_enforce_from`. Until it ends, keys keep working
 * for accounts that have not accepted the current terms or described their
 * project; the portal asks for both straight away.
 */
class ApiTermsDeadline
{
    public static function passed(): bool
    {
        $from = self::from();

        return $from === null || now()->gte($from);
    }

    /** The date to quote while the grace period runs, or null once it has ended. */
    public static function graceEndsOn(): ?string
    {
        return self::passed() ? null : self::from()->format('F j, Y');
    }

    private static function from(): ?Carbon
    {
        $from = config('api.terms_enforce_from');

        return $from ? Carbon::parse($from)->startOfDay() : null;
    }
}
