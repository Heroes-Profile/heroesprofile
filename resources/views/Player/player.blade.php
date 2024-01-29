@extends('layouts.app', $bladeGlobals)    
@section('title', $battletag . "'s Main Player Stats")
@section('meta_keywords', 'Player Stats, Hero Statistics, Player Profile, Player Performance')
@section('meta_description', 'Explore the main player stats of ' . $battletag . ', including hero statistics, player profile, and performance metrics.')
@section('content')
  <player-stats 
    :settinghero="{{ json_encode($settingHero) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :filters="{{ json_encode($filters) }}"
    :season="{{ json_encode($season) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  ></player-stats>
@endsection
