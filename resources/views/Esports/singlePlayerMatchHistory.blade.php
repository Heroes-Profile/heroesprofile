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
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esportfilters="{{ json_encode($esportfilters) }}"
    :playermatchtablestyle="{{ json_encode($playermatchtablestyle ?? null) }}"
    :authenticated="{{ json_encode(auth()->check()) }}"

  >
  </esports-player-match-history>
@endsection
