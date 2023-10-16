<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplayDraftOrder extends Model
{
    protected $table = 'replay_draft_order';

    protected $primaryKey = 'replay_draft_order_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
