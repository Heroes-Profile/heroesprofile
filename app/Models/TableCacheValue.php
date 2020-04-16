<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableCacheValue extends Model
{
  protected $table = 'table_cache_value';
  protected $primaryKey = 'table_cache_value_id';
  public $timestamps = false;
  protected $connection= 'mysql_cache';

  public function scopeFilters($query, $season){
    $query->where('season', $season);
    return $query;
  }
}
