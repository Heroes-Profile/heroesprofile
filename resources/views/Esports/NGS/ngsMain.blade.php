@extends('layouts.app', $bladeGlobals)    

@section('title', __('NGS (Nexus Gaming Series) Stats – Heroes of the Storm'))
@section('meta_keywords', 'NGS league, Nexus Gaming Series, Heroes of the Storm, NGS esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Heroes of the Storm statistics and comparison for the Nexus Gaming Series: hero stats, divisions, standings, teams, players and match history.')

@section('content')
  <ngs-main 
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :urlparameters="{{ json_encode($urlparameters) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  >
  </ngs-main>
@endsection
