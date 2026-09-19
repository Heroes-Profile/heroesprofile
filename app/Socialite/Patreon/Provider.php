<?php

namespace App\Socialite\Patreon;

use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Patreon\Provider as BaseProvider;

/**
 * Patreon's v2 identity endpoint omits attributes that have no value, e.g. no full_name
 * on accounts that never set one. The vendor mapping indexes them directly and throws.
 */
class Provider extends BaseProvider
{
    protected function mapUserToObject(array $user)
    {
        $attributes = $user['data']['attributes'] ?? [];

        return (new User)->setRaw($user)->map([
            'id' => $user['data']['id'],
            'nickname' => Arr::get($attributes, 'vanity') ?? Arr::get($attributes, 'full_name'),
            'name' => Arr::get($attributes, 'full_name'),
            'email' => Arr::get($attributes, 'email'),
            'avatar' => Arr::get($attributes, 'image_url'),
        ]);
    }
}
