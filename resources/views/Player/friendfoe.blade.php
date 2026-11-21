@extends('layouts.app', $bladeGlobals)    
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => 'Friends & Foes'])
@section('title', __(':battletag (:region) Friends & Foes', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Friend and Foe Data, Player Connections, Player Statistics')
@section('meta_description', 'Friend and foe data for ' . $battletag . ' (' . $regionName . '): who they play the most Heroes of the Storm games with and against, and how those games go.')
@section('content')
  <friend-foe  
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}"
    :season="{{ json_encode($season) }}"
    :gametype="{{ json_encode($game_type) }}"
    :gamemap="{{ json_encode($game_map) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
  ></friend-foe>
@endsection
