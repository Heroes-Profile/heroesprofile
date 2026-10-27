<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    protected $table = 'poll_options';

    protected $primaryKey = 'poll_option_id';

    protected $connection = 'heroesprofile_polls';

    public $timestamps = false;

    protected $fillable = [
        'poll_id',
        'label',
        'description',
        'sort_order',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class, 'poll_id', 'poll_id');
    }
}
