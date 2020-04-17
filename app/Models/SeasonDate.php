<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonDate extends Model
{
  protected $fillable = ['id', 'year', 'season', 'start_date', 'end_date'];
  protected $table = 'season_dates';
  public $timestamps = false;
}
