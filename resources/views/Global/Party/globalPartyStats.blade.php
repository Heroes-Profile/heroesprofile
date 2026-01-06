@extends('layouts.app', $bladeGlobals)    
@section('title', 'Global Party Stats')
@section('meta_keywords', 'heroes of the storm party stats, hots party stats, party stats, hero stacks, stack size, stack performance, heroes of the storm stacks')
@section('meta_description', 'Heroes of the Storm party and stack size statistics. Explore party stats, including stack size and performance. Analyze how different stack sizes fare against others on Heroes Profile.')

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