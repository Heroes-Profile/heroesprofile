@extends('layouts.app')
@section('title', $battletag . " All Hero Stats")
@section('meta_keywords', 'Player All Hero Stats, Hero Statistics, Player Stats')
@section('meta_description', 'Explore the statistics and data for all heroes played by a player. Analyze player performance and stats across different heroes.')
@section('content')
  <player-heroes-all-stats 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :accountlevel="{{ json_encode($account_level) }}" 
    :region="{{ json_encode($region) }}"
    :regionsmap="{{ json_encode(session('regions')) }}"
  ></player-heroes-all-stats>
@endsection
