<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Keyed by the `subscription_plans.plan_id` the endpoint registry uses.
    |
    | `stripe_price` identifies the Stripe Price object and differs between test
    | and live mode, so it belongs in env. The values below are the legacy Stripe
    | plan ids the old site issued — verify each against Stripe before taking
    | payments through Cashier.
    |
    | `price` is a DISPLAY FALLBACK, not the definition. Stripe holds the real
    | amount in the Price object's unit_amount, and step 13 reads it from there
    | and caches it. This value is only used if that lookup fails.
    |
    | Retired and deliberately absent: 7 (ccl), 8/9 (twitch_2hr, twitch_4hr),
    | 10 (masters_clash).
    |
    */

    'plans' => [
        1 => [
            'key' => 'basic',
            'name' => 'Basic',
            'price' => 5,
            'stripe_price' => env('STRIPE_PRICE_ID_BASIC', 'b_789502431'),
            'paid' => true,
        ],
        2 => [
            'key' => 'intermediate',
            'name' => 'Intermediate',
            'price' => 10,
            'stripe_price' => env('STRIPE_PRICE_ID_INTERMEDIATE', 'i_789502431'),
            'paid' => true,
        ],
        3 => [
            'key' => 'developer',
            'name' => 'Developer',
            'price' => 25,
            'stripe_price' => env('STRIPE_PRICE_ID_DEVELOPER', 'd_789502431'),
            'paid' => true,
        ],

        // Comped tiers. Granted by hand via the users.*_approved columns rather
        // than purchased, so they are not offered for sale.
        4 => [
            'key' => 'partner',
            'name' => 'Partner',
            'price' => 0,
            'stripe_price' => 'p_789502431',
            'paid' => false,
        ],
        5 => [
            'key' => 'ngs',
            'name' => 'NGS',
            'price' => 0,
            'stripe_price' => 'n_789502431',
            'paid' => false,
        ],
        6 => [
            'key' => 'heroes_lounge',
            'name' => 'Heroes Lounge',
            'price' => 0,
            'stripe_price' => 'h_789502431',
            'paid' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Comped Access
    |--------------------------------------------------------------------------
    |
    | Flag on `users` => the plan it grants, in precedence order. An account with
    | one of these set resolves to that plan without any Stripe subscription.
    |
    | The old site required a subscription row before it would look at these
    | flags, so comped grants were faked with placeholder rows sharing one
    | customer id. Deriving the plan here removes the need for those.
    |
    | `d_approved` is deliberately absent: it makes the Developer tier
    | selectable, it does not grant it. Those accounts still pay.
    |
    */

    'comped_flags' => [
        'p_approved' => 4,
        'n_approved' => 5,
        'h_approved' => 6,
    ],

];
