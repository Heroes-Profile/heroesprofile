<?php

namespace App\Models\Api;

use Laravel\Cashier\Subscription;

class CashierSubscription extends Subscription
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'cashier_subscriptions';

    /**
     * Would otherwise derive `cashier_subscription_id` from the class name.
     * Both the items relation and its inverse use this.
     */
    public function getForeignKey()
    {
        return 'subscription_id';
    }
}
