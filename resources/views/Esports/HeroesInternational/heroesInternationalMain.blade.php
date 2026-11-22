@extends('layouts.app', $bladeGlobals)    

@section('title', __('Heroes International League Stats'))
@section('canonical', url('/Esports/HeroesInternational') . '?tournament=main')
@section('meta_keywords', 'Heroes International league, Heroes International, Heroes of the Storm, Heroes International esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Heroes of the Storm statistics and comparison for the Heroes International league: hero stats, teams, players and match history.')

@section('content')
  <heroes-international-main
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :tournament="{{ json_encode($tournament) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"
  ></heroes-international-main>
  @include('Esports.contactSection')
@endsection
