@extends('layouts.app', $bladeGlobals)    
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
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >  
  </player-match-history>
@endsection
