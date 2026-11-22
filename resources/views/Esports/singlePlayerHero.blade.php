@extends('layouts.app', $bladeGlobals)    
@php($league = (empty($esport) || $esport === 'Other') ? (is_string($series ?? null) && $series !== '' ? $series : (optional($series ?? null)->name ?? 'Esports')) : (['MastersClash' => 'Masters Clash', 'HeroesInternational' => (($tournament ?? null) === 'nationscup' ? 'Heroes International Nations Cup' : 'Heroes International'), 'NutCup' => 'Nut Cup'][$esport] ?? $esport))

@section('title', __(':battletag :hero Stats – :league', ['battletag' => $battletag, 'hero' => $hero, 'league' => $league]))
@section('meta_keywords', 'Heroes Profile, player hero stats, player performance, Heroes of the Storm, specific heroes')
@section('meta_description', $hero . ' stats for ' . $battletag . ' in ' . $league . ' Heroes of the Storm esports: win rate, games, talents and matchups.')

@section('content')
  <esports-player-hero-stats 
    :filters="{{ json_encode($filters) }}"
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :division="{{ json_encode($division) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizz_id="{{ json_encode($blizz_id) }}" 
    :season="{{ json_encode($season) }}" 
    :hero="{{ json_encode($hero) }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></esports-player-hero-stats>
@endsection
