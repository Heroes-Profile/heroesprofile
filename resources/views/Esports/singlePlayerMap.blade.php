@extends('layouts.app', $bladeGlobals)    
@php($league = (empty($esport) || $esport === 'Other') ? (is_string($series ?? null) && $series !== '' ? $series : (optional($series ?? null)->name ?? 'Esports')) : (['MastersClash' => 'Masters Clash', 'HeroesInternational' => (($tournament ?? null) === 'nationscup' ? 'Heroes International Nations Cup' : 'Heroes International'), 'NutCup' => 'Nut Cup'][$esport] ?? $esport))

@section('title', __(':battletag :map Stats – :league', ['battletag' => $battletag, 'map' => $game_map, 'league' => $league]))
@section('meta_keywords', 'Heroes Profile, player map stats, player performance, Heroes of the Storm, specific maps')
@section('meta_description', 'Map stats for ' . $battletag . ' on ' . $game_map . ' in ' . $league . ' Heroes of the Storm esports: win rate, games and the heroes they play there.')

@section('content')
  <esports-player-map-stats 
    :filters="{{ json_encode($filters) }}"
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :division="{{ json_encode($division) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizz_id="{{ json_encode($blizz_id) }}" 
    :season="{{ json_encode($season) }}" 
    :game_map="{{ json_encode($game_map) }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></esports-player-map-stats>
@endsection
