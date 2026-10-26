@extends('layouts.app', $bladeGlobals)    

@if ($userinput)
  @section('title', $userinput["name"] . ' Counters & Matchups')
  @section('meta_description', $userinput["name"] . ' counters and matchups for Heroes of the Storm. Win rates against every enemy hero and alongside every ally, from real games on the latest patch.')
@else
  @section('title', 'Hero Counters & Matchups')
  @section('meta_description', 'Heroes of the Storm hero matchup statistics and counters. Explore hero matchup statistics to see which heroes perform well against others. Analyze hero counters and matchups to improve your gameplay on Heroes Profile.')
@endif


@section('meta_keywords', 'heroes of the storm matchups, hots matchups, hero matchup stats, hero counters, hero performance, heroes of the storm counters')

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