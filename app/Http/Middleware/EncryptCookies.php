<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        'main_search_account',
        'alt_search_account1',
        'alt_search_account2',
        'alt_search_account3',
        'battlenet_region',
    ];
}
