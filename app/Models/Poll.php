<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $table = 'polls';

    protected $primaryKey = 'poll_id';

    protected $connection = 'heroesprofile_polls';

    protected $fillable = [
        'slug',
        'title',
        'description',
        'allow_multiple',
        'closes_at',
    ];

    protected $casts = [
        'allow_multiple' => 'boolean',
        'closes_at' => 'datetime',
    ];

    public function options()
    {
        return $this->hasMany(PollOption::class, 'poll_id', 'poll_id')->orderBy('sort_order')->orderBy('poll_option_id');
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class, 'poll_id', 'poll_id');
    }

    public function isOpen(): bool
    {
        return $this->closes_at === null || $this->closes_at->isFuture();
    }

    /**
     * Voter total and votes per option id.
     */
    public function results(): array
    {
        $counts = PollVoteOption::query()
            ->join('poll_votes', 'poll_votes.poll_vote_id', '=', 'poll_vote_options.poll_vote_id')
            ->where('poll_votes.poll_id', $this->poll_id)
            ->groupBy('poll_vote_options.poll_option_id')
            ->selectRaw('poll_vote_options.poll_option_id, COUNT(*) AS votes')
            ->pluck('votes', 'poll_option_id')
            ->map(fn ($votes) => (int) $votes);

        return [
            'total' => $this->votes()->count(),
            'counts' => $counts,
        ];
    }
}
