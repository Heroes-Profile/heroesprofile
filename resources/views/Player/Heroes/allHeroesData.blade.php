@extends('layouts.app', $bladeGlobals)    
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => 'Hero Stats'])
@section('title', __(':battletag (:region) Hero Stats', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Player All Hero Stats, Hero Statistics, Player Stats')
@section('meta_description', 'All Heroes of the Storm heroes played by ' . $battletag . ' (' . $regionName . '): win rate and games for every hero. Pick a hero to see its full stats.')
@section('content')
  <player-heroes-all-stats 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :accountlevel="{{ json_encode($account_level) }}" 
    :region="{{ json_encode($region) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :urlparameters="{{ json_encode((object) request()->query()) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-heroes-all-stats>
@endsection
