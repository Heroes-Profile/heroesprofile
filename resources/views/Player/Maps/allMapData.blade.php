@extends('layouts.app')
@section('title', 'Player Map Stats')
@section('title', $battletag . " All Map Stats")
@section('meta_keywords', 'Player Map Stats, Map Statistics, Player Stats')
@section('meta_description', 'Explore the statistics and data for all maps played by a player. Analyze player performance and stats for different maps.')
@section('content')
  <player-maps-all-stats 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"
    :accountlevel="{{ json_encode($account_level) }}" 
    :regionsmap="{{ json_encode(session('regions')) }}"
    :is-patreon="{{ json_encode($patreon) }}"
  ></player-maps-all-stats>
@endsection
