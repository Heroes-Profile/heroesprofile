@extends('layouts.app')
@section('title', $battletag . " " . $map . " Stats")
@section('meta_keywords', 'Player Single Map Stats, Map Statistics, Player Stats')
@section('meta_description', 'Explore the statistics and data for a single map played by a player. Analyze player performance and stats for the selected map.')
@section('content')
  <player-map-single-stats 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ $region }}" 
    :map="{{ json_encode($map) }}" 
    :mapobject="{{ json_encode($mapobject) }}" 
    :regions="{{ json_encode($regions) }}"
    :regionsmap="{{ json_encode(session('regions')) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  ></player-map-single-stats>
@endsection
