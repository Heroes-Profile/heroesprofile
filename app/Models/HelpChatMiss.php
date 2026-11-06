<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Help chat questions the FAQ didn't answer, for improving the FAQ
class HelpChatMiss extends Model
{
    protected $table = 'help_chat_misses';

    protected $primaryKey = 'help_chat_miss_id';

    protected $connection = 'heroesprofile_logs';

    public $timestamps = false;

    protected $fillable = [
        'battlenet_accounts_id',
        'question',
        'reason',
        'faq_question',
        'answer',
        'date_time',
    ];

    protected $casts = [
        'battlenet_accounts_id' => 'integer',
        'date_time' => 'datetime',
    ];
}
