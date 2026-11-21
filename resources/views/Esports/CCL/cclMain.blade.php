@extends('layouts.app', $bladeGlobals)    

@section('title', __('CCL (Community Clash League) Stats – Heroes of the Storm'))
@section('meta_keywords', 'CCL esports, Heroes of the Storm, CCL league, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Heroes of the Storm statistics and comparison for the Community Clash League: hero stats, teams, players and match history.')

@section('content')
  <ccl-main 
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :organizations="{{ json_encode($organizations) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"
  >
  </ccl-main>
@endsection
