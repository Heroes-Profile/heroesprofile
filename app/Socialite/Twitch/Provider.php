<?php

namespace App\Socialite\Twitch;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

/**
 * Twitch sign-in, used only to prove which channel a streamer owns.
 *
 * Written here rather than pulling in socialiteproviders/twitch: it is three URLs,
 * and no scopes are requested — the user id and login are all we keep.
 */
class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'TWITCH';

    protected $scopes = [];

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://id.twitch.tv/oauth2/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://id.twitch.tv/oauth2/token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.twitch.tv/helix/users', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Client-Id' => $this->clientId,
            ],
        ]);

        return json_decode((string) $response->getBody(), true)['data'][0] ?? [];
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'] ?? null,
            'nickname' => $user['login'] ?? null,
            'name' => $user['display_name'] ?? null,
            'email' => null,
            'avatar' => $user['profile_image_url'] ?? null,
        ]);
    }

    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
