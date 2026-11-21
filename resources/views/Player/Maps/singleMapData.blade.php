@extends('layouts.app', $bladeGlobals)    
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => $map])
@section('title', __(':battletag (:region) :map Stats', ['battletag' => $battletag, 'region' => $regionName, 'map' => $map]))
@section('meta_keywords', 'Player Single Map Stats, Map Statistics, Player Stats')
@section('meta_description', 'Map stats for ' . $battletag . ' (' . $regionName . ') on ' . $map . ' in Heroes of the Storm: win rate, games played and the heroes they play there.')
@section('content')
  <player-map-single-stats 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"  
    :map="{{ json_encode($map) }}" 
    :mapobject="{{ json_encode($mapobject) }}" 
    :regions="{{ json_encode($bladeGlobals['regions']) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :replay-viewer-allowed="{{ json_encode($replayViewerAllowed) }}"
    :analytics-cutover="{{ json_encode($analyticsCutover) }}"
    :replay-download-blocked="{{ json_encode($replayDownloadBlocked) }}"

  ></player-map-single-stats>
@endsection
