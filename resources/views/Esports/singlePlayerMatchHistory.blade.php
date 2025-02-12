@extends('layouts.app', $bladeGlobals)    
@section('title', $battletag . " Match History")
@section('meta_keywords', 'Match History, Player Matches, Game Results, Match Stats')
@section('meta_description', 'Explore the match history of ' . $battletag . ', including game details, heroes played, talents, wins, and losses.')
@section('content')
  <esports-player-match-history 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :season="{{ json_encode($season) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  >  
  </esports-player-match-history>
@endsection
