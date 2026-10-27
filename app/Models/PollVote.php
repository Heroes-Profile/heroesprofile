<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    protected $table = 'poll_votes';

    protected $primaryKey = 'poll_vote_id';

    protected $connection = 'heroesprofile_polls';

    protected $fillable = [
        'poll_id',
        'battlenet_accounts_id',
        'comment',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class, 'poll_id', 'poll_id');
    }

    public function options()
    {
        return $this->hasMany(PollVoteOption::class, 'poll_vote_id', 'poll_vote_id');
    }
}
