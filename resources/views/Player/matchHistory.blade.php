@extends('layouts.app')
@section('title', $battletag . " Match History")
@section('meta_keywords', 'Match History, Player Matches, Game Results, Match Stats')
@section('meta_description', 'Explore the match history of ' . $battletag . ', including game details, heroes played, talents, wins, and losses.')
@section('content')
  <player-match-history 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :regionsmap="{{ json_encode(session('regions')) }}"
  >  
  </player-match-history>
@endsection
