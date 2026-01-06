@extends('layouts.app', $bladeGlobals)    

@if ($userinput)
  @section('title', $userinput["name"] . ' Global Hero Matchup Stats')
@else
  @section('title', 'Global Hero Matchup Stats')
@endif


@section('meta_keywords', 'heroes of the storm matchups, hots matchups, hero matchup stats, hero counters, hero performance, heroes of the storm counters')
@section('meta_description', 'Heroes of the Storm hero matchup statistics and counters. Explore hero matchup statistics to see which heroes perform well against others. Analyze hero counters and matchups to improve your gameplay on Heroes Profile.')

@section('content')
  <global-matchups-stats 
    :heroes="{{ json_encode($heroes) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :filters="{{ json_encode($filters) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"

  >
  </global-matchups-stats>
@endsection