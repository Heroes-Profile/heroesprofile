@extends('layouts.app', $bladeGlobals)    
@php($regionName = $leagueName ?? ($bladeGlobals['regions'][$region] ?? $region))
@include('Player.structuredData', ['page' => 'Role Stats'])
@section('title', __(':battletag (:region) Role Stats', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Player Role Stats, Role Statistics, Player Stats')
@section('meta_description', 'All Heroes of the Storm roles played by ' . $battletag . ' (' . $regionName . '): win rate and games for every role. Pick a role to see its full stats.')
@section('content')
  <player-roles-all-stats 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :accountlevel="{{ json_encode($account_level) }}" 
    :region="{{ json_encode($region) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-roles-all-stats>
@endsection
