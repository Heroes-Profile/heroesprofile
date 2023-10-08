@extends('layouts.app')

@section('title', 'Player Hero Stats Esports')
@section('meta_keywords', 'Heroes Profile, player hero stats, player performance, Heroes of the Storm, specific heroes')
@section('meta_description', 'Explore player statistics and performance with specific heroes in various esports leagues on Heroes Profile. Analyze player data, track their performance with specific heroes, and compare stats across different esports leagues.')

@section('content')
  <esports-player-hero-stats 
    :esport="{{ json_encode($esport) }}" 
    :division="{{ json_encode($division) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizz_id="{{ json_encode($blizz_id) }}" 
    :season="{{ json_encode($season) }}" 
    :hero="{{ json_encode($hero) }}" 
  ></esports-player-hero-stats>
@endsection
