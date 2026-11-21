@extends('layouts.app', $bladeGlobals)    
@php($league = (empty($esport) || $esport === 'Other') ? (is_string($series ?? null) && $series !== '' ? $series : (optional($series ?? null)->name ?? 'Esports')) : (['MastersClash' => 'Masters Clash', 'HeroesInternational' => 'Heroes International', 'NutCup' => 'Nut Cup'][$esport] ?? $esport))

@section('title', __(':battletag – :league Player Stats', ['battletag' => $battletag, 'league' => $league]))
@section('meta_keywords', 'Heroes Profile, esports player stats, esports leagues, player performance, Heroes of the Storm')
@section('meta_description', $battletag . "'s Heroes of the Storm esports stats in " . $league . ': heroes, maps, teams and match history.')

@section('content')
  <esports-player-stats 
    :filters="{{ json_encode($filters) }}"
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :division="{{ json_encode($division) }}" 
    :team="{{ json_encode($team ?? null) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizz_id="{{ json_encode($blizz_id) }}" 
    :season="{{ json_encode($season) }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></esports-player-stats>
@endsection
