<?php

namespace App\Models\Api;

use Laravel\Cashier\Subscription;

class CashierSubscription extends Subscription
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'cashier_subscriptions';
}
