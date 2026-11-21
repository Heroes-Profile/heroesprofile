@extends('layouts.app', $bladeGlobals)    
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => 'Match History'])
@section('title', __(':battletag (:region) Match History', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Match History, Player Matches, Game Results, Match Stats')
@section('meta_description', 'Every Heroes of the Storm game ' . $battletag . ' (' . $regionName . ') has on Heroes Profile: heroes, talents, maps, results and replays, filterable by game mode, season, hero and friend or foe.')
@section('content')
  <player-match-history 
    :filters="{{ json_encode($filters) }}" 
    :playermatchtablestyle="{{ json_encode($playermatchtablestyle) }}" 
    :authenticated="{{ json_encode(auth()->check()) }}"
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :showcustomgames="{{ json_encode($showcustomgames) }}" 
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"
    :analytics-cutover="{{ json_encode($analyticsCutover) }}"
    :replay-download-blocked="{{ json_encode($replayDownloadBlocked) }}"
  >  
  </player-match-history>
@endsection
