@extends('layouts.app')
@section('title', $battletag . " Matchup Data")
@section('meta_keywords', 'Matchup Data, Hero Matchups, Hero Performance, Win Rate, Heroes Played')
@section('meta_description', 'Explore the matchup data of ' . $battletag . ', including hero performance, win rates, and heroes played well against or with.')
@section('content')
  <player-matchup 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"
  ></player-matchup>
@endsection
