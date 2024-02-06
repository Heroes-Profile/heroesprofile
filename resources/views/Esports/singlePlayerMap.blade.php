@extends('layouts.app', $bladeGlobals)    

@section('title', 'Player Map Stats Esports')
@section('meta_keywords', 'Heroes Profile, player map stats, player performance, Heroes of the Storm, specific maps')
@section('meta_description', 'Explore player statistics and performance on specific maps in various esports leagues on Heroes Profile. Analyze player data, track their performance on specific maps, and compare stats across different esports leagues.')

@section('content')
  <esports-player-map-stats 
    :esport="{{ json_encode($esport) }}" 
    :division="{{ json_encode($division) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizz_id="{{ json_encode($blizz_id) }}" 
    :season="{{ json_encode($season) }}" 
    :game_map="{{ json_encode($game_map) }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></esports-player-map-stats>
@endsection
