@extends('layouts.app', $bladeGlobals)    
@php($regionName = $leagueName ?? ($bladeGlobals['regions'][$region] ?? $region))
@include('Player.structuredData', ['page' => $hero])
@section('title', __(':battletag (:region) :hero Stats', ['battletag' => $battletag, 'region' => $regionName, 'hero' => $hero]))
@section('meta_keywords', 'Player Hero Stats, Hero Statistics, Player Stats')
@section('meta_description', $hero . ' stats for ' . $battletag . ' (' . $regionName . ') in Heroes of the Storm: win rate, games played, MMR, talents, matchups and maps on ' . $hero . '.')
@section('content')
  <player-hero-single-stats 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"  
    :hero="{{ json_encode($hero) }}" 
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :heroobject="{{ json_encode($heroObject) }}"
    :heroes="{{ json_encode($heroes) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :replay-viewer-allowed="{{ json_encode($replayViewerAllowed) }}"
    :analytics-cutover="{{ json_encode($analyticsCutover) }}"
    :replay-download-blocked="{{ json_encode($replayDownloadBlocked) }}"

  ></player-hero-single-stats>
@endsection
