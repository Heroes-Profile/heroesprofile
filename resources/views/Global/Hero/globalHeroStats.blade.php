@extends('layouts.app', $bladeGlobals)    

@section('title', 'Heroes of the Storm Tier List & Hero Win Rates')
@section('meta_keywords', 'heroes of the storm tier list, hots tier list, heroes of the storm statistics, heroes of the storm stats, hots statistics, hero win rates, pick rate, ban rate, hero influence, win rate confidence, hero performance')
@section('meta_description', 'Heroes of the Storm tier list from real games. Win rate, pick rate, ban rate and influence for every hero on the latest patch, filterable by rank, map and game mode.')

@section('content')
  <global-hero-stats 
    :filters="{{ json_encode($filters) }}" 
    :heroes="{{ json_encode($heroes) }}" 
    :urlparameters="{{ json_encode($urlparameters) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </global-hero-stats>
@endsection
