@extends('layouts.app', $bladeGlobals)    

@section('title', __('Heroes International Nations Cup Stats'))
@section('canonical', url('/Esports/HeroesInternational') . '?tournament=nationscup')
@section('meta_keywords', 'Heroes International Nations Cup league, Heroes International Nations Cup, Heroes of the Storm, Heroes International Nations Cup esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Heroes of the Storm statistics and comparison for the Heroes International Nations Cup: hero stats, national teams, players and match history.')

@section('content')
  <heroes-international-nations-cup
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :tournament="{{ json_encode($tournament) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"

  ></heroes-international-nations-cup>
  @include('Esports.contactSection')
@endsection
