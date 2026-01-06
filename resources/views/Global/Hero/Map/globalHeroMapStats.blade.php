@extends('layouts.app', $bladeGlobals)    

@if ($userinput)
  @section('title', $userinput["name"] . ' Global Hero Map Stats')
@else
  @section('title', 'Global Hero Map Stats')
@endif



@section('meta_keywords', 'heroes of the storm map stats, hots map stats, hero map stats, hero performance, map synergy, map picks, map win rates, heroes of the storm maps')
@section('meta_description', 'Heroes of the Storm hero and map statistics. Explore global hero-map statistics to discover hero performance on various maps, map synergy, hero picks, and win rates. Filter and analyze hero-map data to make informed decisions on Heroes Profile.')

@section('content')
  <global-hero-map-stats 
    :heroes="{{ json_encode($heroes) }}"  
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"

  >
  </global-hero-map-stats>
@endsection