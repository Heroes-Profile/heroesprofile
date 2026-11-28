@extends('layouts.app', $bladeGlobals)    

@section('title', __('Heroes of the Storm Team Compositions & Win Rates'))
@section('meta_keywords', 'heroes of the storm compositions, hots compositions, hero compositions, composition stats, hero roles, win rates, team compositions, heroes of the storm team comps')
@section('meta_description', 'Heroes of the Storm team composition win rates from real games: every role combination with its win rate, popularity and top heroes, by patch, rank, map and game mode.')

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
