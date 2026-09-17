<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BattletagInputProhibitCharacters implements Rule
{
    /**
     * What a battletag can contain: letters in any script, digits, and an optional
     * #discriminator (partial while typing). Allowlisted rather than blocklisted:
     * queries are parameterised, and the old SQL-keyword list refused real names
     * (Or, Drop, Sleep) while letting the LIKE wildcard _ through.
     */
    private const PATTERN = '/^[\p{L}\p{M}\p{N}]+(#\d*)?$/u';

    public function passes($attribute, $value)
    {
        return is_string($value) && preg_match(self::PATTERN, $value) === 1;
    }

    public function message()
    {
        return 'The :attribute must not contain prohibited characters or patterns.';
    }
}
