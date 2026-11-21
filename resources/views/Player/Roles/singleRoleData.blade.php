@extends('layouts.app', $bladeGlobals)    
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => $role])
@section('title', __(':battletag (:region) :role Stats', ['battletag' => $battletag, 'region' => $regionName, 'role' => $role]))
@section('meta_keywords', 'Player Role Stats, Role Statistics, Player Stats')
@section('meta_description', 'Role stats for ' . $battletag . ' (' . $regionName . ') as ' . $role . ' in Heroes of the Storm: win rate, games played and the heroes they play in the role.')
@section('content')
  <player-role-single-stats 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"  
    :role="{{ json_encode($role) }}" 
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :replay-viewer-allowed="{{ json_encode($replayViewerAllowed) }}"
    :analytics-cutover="{{ json_encode($analyticsCutover) }}"
    :replay-download-blocked="{{ json_encode($replayDownloadBlocked) }}"

  ></player-role-single-stats>
@endsection
