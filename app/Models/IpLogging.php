<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpLogging extends Model
{
  protected $table = 'ip_logging';

  protected $primaryKey = 'ip_logging_id';

  protected $connection = 'heroesprofile_logs';

  public $timestamps = false;

  protected $fillable = ['ip', 'page', 'user_agent'];
}
