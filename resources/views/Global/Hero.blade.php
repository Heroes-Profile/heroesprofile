<?php

/*
$data = DB::table('heroesprofile.global_hero_stats')->where([
  ['game_version', '2.48.1.76517'],
  ['game_type', '5'],
  ['league_tier', '1'],
  ['game_map', '3'],
  ['hero_level', '20'],
  ])
  ->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero')
  ->select('heroes.name', 'global_hero_stats.win_loss', DB::raw('SUM(games_played) as games_played'))
  ->groupBy('hero', 'win_loss')
  ->get();
*/

/*
$data = DB::table('heroesprofile.global_hero_stats')
->whereIn('game_version', ['2.48.0.76437', '2.48.1.76517'])
->whereIn('game_type', [1, 5])
->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero')
->select('heroes.name', 'global_hero_stats.win_loss', DB::raw('SUM(games_played) as games_played'))
->groupBy('hero', 'win_loss')
->get();
*/

$query = DB::table('heroesprofile.global_hero_stats');
$query->whereIn('game_version', ['2.48.0.76437', '2.48.1.76517']);
$query->whereIn('game_type', [1, 5]);

$query->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero')
->select('heroes.name', 'global_hero_stats.win_loss', DB::raw('SUM(games_played) as games_played'))
->groupBy('hero', 'win_loss');


$data = $query->get();
//$data = $query->toSql();


print_r($data);
//print_r(json_encode($data, true));

 ?>
