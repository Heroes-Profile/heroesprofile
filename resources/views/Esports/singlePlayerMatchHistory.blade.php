@extends('layouts.app', $bladeGlobals)
@php($league = (empty($esport) || $esport === 'Other') ? (is_string($series ?? null) && $series !== '' ? $series : (optional($series ?? null)->name ?? 'Esports')) : (['MastersClash' => 'Masters Clash', 'HeroesInternational' => 'Heroes International', 'NutCup' => 'Nut Cup'][$esport] ?? $esport))
@section('title', __(':battletag – :league Match History', ['battletag' => $battletag, 'league' => $league]))
@section('meta_keywords', 'Match History, Player Matches, Game Results, Match Stats')
@section('meta_description', 'Every ' . $league . ' game ' . $battletag . ' played in Heroes of the Storm esports, with heroes, talents, maps and results.')
@section('content')
  <esports-player-match-history
    :filters="{{ json_encode($filters) }}"
    :battletag="{{ json_encode($battletag) }}"
    :blizzid="{{ json_encode($blizz_id) }}"
    :esport="{{ json_encode($esport) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esportfilters="{{ json_encode($esportfilters) }}"
    :playermatchtablestyle="{{ json_encode($playermatchtablestyle ?? null) }}"
    :authenticated="{{ json_encode(auth()->check()) }}"

  >
  </esports-player-match-history>
@endsection
