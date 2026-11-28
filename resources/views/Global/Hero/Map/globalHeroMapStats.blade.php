@extends('layouts.app', $bladeGlobals)    

@if ($userinput)
  @section('title', __(':hero Best Maps & Map Win Rates', ['hero' => $userinput["name"]]))
  @section('meta_description', 'The best and worst maps for ' . $userinput["name"] . ' in Heroes of the Storm. Win rate, ban rate and games played on every map, from real games on the latest patch.')
@else
  @section('title', __('Heroes of the Storm Hero Map Win Rates'))
  @section('meta_description', 'Which maps are good for each Heroes of the Storm hero: win rates on every map from real games. Pick a hero to see its best and worst maps.')
@endif



@section('meta_keywords', 'heroes of the storm map stats, hots map stats, hero map stats, hero performance, map synergy, map picks, map win rates, heroes of the storm maps')

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