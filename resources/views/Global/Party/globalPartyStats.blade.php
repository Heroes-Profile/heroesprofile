@extends('layouts.app', $bladeGlobals)    
@section('title', __('Heroes of the Storm Party & Stack Win Rates'))
@section('meta_keywords', 'heroes of the storm party stats, hots party stats, party stats, hero stacks, stack size, stack performance, heroes of the storm stacks')
@section('meta_description', 'Heroes of the Storm win rates by party setup: solo to five-stack, and every party against every enemy party. Filter by patch, rank, game mode and hero to see party data for games with that hero.')

@section('content')
  <global-party-stats 
    :filters="{{ json_encode($filters) }}" 
    :heroes="{{ json_encode($heroes) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"

  >
  </global-party-stats>
@endsection