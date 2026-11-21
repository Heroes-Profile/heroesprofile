@extends('layouts.app', $bladeGlobals)    
@php($league = (empty($esport) || $esport === 'Other') ? (is_string($series ?? null) && $series !== '' ? $series : (optional($series ?? null)->name ?? 'Esports')) : (['MastersClash' => 'Masters Clash', 'HeroesInternational' => 'Heroes International', 'NutCup' => 'Nut Cup'][$esport] ?? $esport))

@section('title', __(':team – :league Team Stats', ['team' => $team, 'league' => $league]))
@section('meta_keywords', 'Heroes Profile, team data, organization data, esports data, team performance, organization performance, Heroes of the Storm')
@section('meta_description', $team . ' in ' . $league . ', Heroes of the Storm esports: roster, hero stats, maps and match history.')

@section('content')
  <esports-single-team 
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :division="{{ json_encode($division) }}" 
    :team="{{ json_encode($team) }}" 
    :season="{{ json_encode($season) }}" 
    :region="{{ isset($region) ? json_encode($region) : 'null' }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :image="{{ json_encode($image) }}" 
    :filters="{{ json_encode($filters) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></esports-single-team>
@endsection
