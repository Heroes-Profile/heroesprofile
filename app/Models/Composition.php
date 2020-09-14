<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Composition extends Model
{
  protected $table = 'compositions';
  protected $primaryKey = 'composition_id';
  public $timestamps = false;

  public function scopeFilters($query, $composition_id){
    $query->where('composition_id', $composition_id);
    return $query;
  }
}
