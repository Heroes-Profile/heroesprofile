<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BattletagInputProhibitCharacters implements Rule
{
    // Block common SQL injection and XSS characters
    protected $prohibitedCharacters = [' ', '?', '%', '\'', '"', '(', ')', ';', '--', '/*', '*/', '<', '>', '=', '|', '\\'];

    // Also check for common SQL injection patterns
    protected $prohibitedPatterns = [
        '/(\bOR\b|\bAND\b)/i', // OR/AND keywords
        '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|EXEC|EXECUTE)\b/i', // SQL keywords
        '/\bwaitfor\b/i', // SQL Server time delay
        '/\bsleep\b/i', // MySQL time delay
        '/\bpg_sleep\b/i', // PostgreSQL time delay
        '/DBMS_PIPE/i', // Oracle time delay
        '/\bif\s*\(/i', // Conditional statements
        '/\bXOR\b/i', // XOR operations
    ];

    public function passes($attribute, $value)
    {
        // Check for prohibited characters
        foreach ($this->prohibitedCharacters as $character) {
            if (strpos($value, $character) !== false) {
                return false;
            }
        }

        // Check for prohibited patterns
        foreach ($this->prohibitedPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute must not contain prohibited characters or patterns.';
    }
}
