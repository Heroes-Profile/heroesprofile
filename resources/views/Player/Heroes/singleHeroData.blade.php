@extends('layouts.app')
@section('title', $battletag . " " . $hero . " Stats")
@section('meta_keywords', 'Player Hero Stats, Hero Statistics, Player Stats')
@section('meta_description', 'Explore the statistics and data for a specific hero played by a player. Analyze player performance and stats for a particular hero.')
@section('content')
  <player-hero-single-stats 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ $region }}" 
    :hero="{{ json_encode($hero) }}" 
    :regionsmap="{{ json_encode(session('regions')) }}"
    :heroobject="{{ json_encode($heroObject) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  ></player-hero-single-stats>
@endsection
