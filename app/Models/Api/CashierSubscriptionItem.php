<?php

namespace App\Models\Api;

use Laravel\Cashier\SubscriptionItem;

class CashierSubscriptionItem extends SubscriptionItem
{
    protected $connection = 'heroesprofile_api';

    protected $table = 'cashier_subscription_items';
}
