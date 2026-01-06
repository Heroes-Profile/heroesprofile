@extends('layouts.app', $bladeGlobals)    

@section('title', 'Global Composition Stats')
@section('meta_keywords', 'heroes of the storm compositions, hots compositions, hero compositions, composition stats, hero roles, win rates, team compositions, heroes of the storm team comps')
@section('meta_description', 'Heroes of the Storm composition statistics and team builds. Analyze hero roles and compositions, view win rates, and discover the most effective hero combinations. Filter and analyze composition data to make informed decisions on Heroes Profile.')

@section('content')
  <compositions-stats 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :heroes="{{ json_encode($heroes) }}" 
    :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"

  >
  </compositions-stats>
@endsection
