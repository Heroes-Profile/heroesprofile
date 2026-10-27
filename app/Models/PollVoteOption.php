<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollVoteOption extends Model
{
    protected $table = 'poll_vote_options';

    protected $connection = 'heroesprofile_polls';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'poll_vote_id',
        'poll_option_id',
    ];
}
