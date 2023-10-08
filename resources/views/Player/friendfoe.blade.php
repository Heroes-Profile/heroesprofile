@extends('layouts.app')
@section('title', $battletag . " Friend and Foe Data")
@section('meta_keywords', 'Friend and Foe Data, Player Connections, Player Statistics')
@section('meta_description', 'Explore the connections and statistics for ' . $battletag . ', including friends and foes. Analyze the players ' . $battletag . ' frequently plays with and against.')
@section('content')
  <friend-foe  
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"
    :season="{{ json_encode($season) }}"
    :gametype="{{ json_encode($game_type) }}"
    :gamemap="{{ json_encode($game_map) }}"
    :regionsmap="{{ json_encode(session('regions')) }}"
  ></friend-foe>
@endsection
