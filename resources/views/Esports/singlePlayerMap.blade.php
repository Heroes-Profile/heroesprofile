@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <esports-player-map-stats 
    :esport="{{ json_encode($esport) }}" 
    :division="{{ json_encode($division) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizz_id="{{ json_encode($blizz_id) }}" 
    :season="{{ json_encode($season) }}" 
    :game_map="{{ json_encode($game_map) }}" 
  ></esports-player-map-stats>
@endsection
