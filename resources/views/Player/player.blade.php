@extends('layouts.app', $bladeGlobals)    
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData')
@section('title', __(':battletag (:region) Heroes of the Storm Player Stats', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Player Stats, Hero Statistics, Player Profile, Player Performance')
@section('meta_description', $battletag . "'s Heroes of the Storm profile (" . $regionName . "): win rate, MMR, most played heroes, maps and roles, and recent games.")
@section('content')
  <player-stats 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :filters="{{ json_encode($filters) }}"
    :season="{{ json_encode($season) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :replay-viewer-allowed="{{ json_encode($replayViewerAllowed) }}"
    :analytics-cutover="{{ json_encode($analyticsCutover) }}"
    :replay-download-blocked="{{ json_encode($replayDownloadBlocked) }}"
    :authenticated="{{ json_encode(auth()->check()) }}"
  ></player-stats>
@endsection
