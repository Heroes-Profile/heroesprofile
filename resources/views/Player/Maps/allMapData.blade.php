@extends('layouts.app', $bladeGlobals)    
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => 'Map Stats'])
@section('title', __(':battletag (:region) Map Stats', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Player Map Stats, Map Statistics, Player Stats')
@section('meta_description', 'All Heroes of the Storm maps played by ' . $battletag . ' (' . $regionName . '): win rate and games on every map. Pick a map to see its full stats.')
@section('content')
  <player-maps-all-stats 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"
    :accountlevel="{{ json_encode($account_level) }}" 
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :urlparameters="{{ json_encode((object) request()->query()) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-maps-all-stats>
@endsection
