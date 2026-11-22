@extends('layouts.app', $bladeGlobals)    
@php($regionName = $leagueName ?? ($bladeGlobals['regions'][$region] ?? $region))
@include('Player.structuredData', ['page' => 'Hero Matchups'])
@section('title', __(':battletag (:region) Hero Matchups', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Matchup Data, Hero Matchups, Hero Performance, Win Rate, Heroes Played')
@section('meta_description', 'Heroes of the Storm matchups for ' . $battletag . ' (' . $regionName . '): win rates with and against every hero.')
@section('content')
  <player-matchup 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-matchup>
@endsection
