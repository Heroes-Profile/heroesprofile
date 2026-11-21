@extends('layouts.app', $bladeGlobals)    
@php($league = (empty($esport) || $esport === 'Other') ? (is_string($series ?? null) && $series !== '' ? $series : (optional($series ?? null)->name ?? 'Esports')) : (['MastersClash' => 'Masters Clash', 'HeroesInternational' => 'Heroes International', 'NutCup' => 'Nut Cup'][$esport] ?? $esport))
@section('title', __(':team – :league Match History', ['team' => $team, 'league' => $league]))
@section('meta_keywords', 'Team & Organization Match History')
@section('meta_description', 'Every ' . $league . ' game ' . $team . ' played in Heroes of the Storm esports, with heroes, maps and results.')

@section('content')
  <esports-match-history 
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :division="{{ json_encode($division) }}" 
    :team="{{ json_encode($team) }}" 
    :season="{{ json_encode($season) }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :type="{{ json_encode($type) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></esports-match-history>
@endsection
