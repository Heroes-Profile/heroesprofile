<?php

namespace App;
use Illuminate\Support\Facades\DB;

$maps = \GlobalFunctions::instance()->getHeroes("name");

$map_names = array_keys($maps);
$map_ids = array_values($maps);

print_r($map_ids);

 ?>
