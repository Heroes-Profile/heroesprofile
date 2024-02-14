@extends('layouts.app', $bladeGlobals)    
@section('title', $battletag . " Matchup Data")
@section('meta_keywords', 'Matchup Data, Hero Matchups, Hero Performance, Win Rate, Heroes Played')
@section('meta_description', 'Explore the matchup data of ' . $battletag . ', including hero performance, win rates, and heroes played well against or with.')
@section('content')
  <player-matchup 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-matchup>
@endsection
