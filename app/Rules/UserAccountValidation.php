<?php

namespace App\Rules;

use App\Models\BattlenetAccount;
use Illuminate\Contracts\Validation\Rule;

class UserAccountValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if the given user ID exists in the battlenet_accounts table
        return BattlenetAccount::where('battlenet_id', $value['battlenet_id'])->exists();
    }

    public function message()
    {
        return 'The selected user account is invalid.';
    }
}
