@extends('layouts.app', $bladeGlobals)    
@section('title', 'Player Map Stats')
@section('title', $battletag . " All Map Stats")
@section('meta_keywords', 'Player Map Stats, Map Statistics, Player Stats')
@section('meta_description', 'Explore the statistics and data for all maps played by a player. Analyze player performance and stats for different maps.')
@section('content')
  <player-maps-all-stats 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"
    :accountlevel="{{ json_encode($account_level) }}" 
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-maps-all-stats>
@endsection
