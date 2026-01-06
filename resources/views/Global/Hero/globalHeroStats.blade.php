@extends('layouts.app', $bladeGlobals)    

@section('title', 'Global Hero Stats')
@section('meta_keywords', 'heroes of the storm statistics, heroes of the storm stats, hots statistics, hero win rates, pick rate, ban rate, hero influence, win rate confidence, hero performance')
@section('meta_description', 'Comprehensive Heroes of the Storm hero statistics and analytics. Explore global hero win rates, pick rates, ban rates, hero influence, and performance metrics. Filter and analyze Heroes of the Storm hero statistics to make informed decisions on Heroes Profile.')

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
