@extends('layouts.app', $bladeGlobals)    
@php($regionName = $leagueName ?? ($bladeGlobals['regions'][$region] ?? $region))
@include('Player.structuredData', ['page' => 'Talent Stats & Builds'])
@section('title', __(':battletag (:region) Talent Stats & Builds', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Player Talents, Talent Statistics, Talent Performance, Talent Builds, Talent Win Rate')
@section('meta_description', 'Talent stats and builds played by ' . $battletag . ' (' . $regionName . ') in Heroes of the Storm, with win rates for every talent and build.')
@section('content')
  <player-talents 
    :battletag="{{ json_encode($battletag) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :filters="{{ json_encode($filters) }}"
    :heroes="{{ json_encode($heroes) }}"
    :talentimages="{{ json_encode($talentimages) }}"  
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :esport="{{ json_encode($esport ?? null) }}"
    :esportfilters="{{ json_encode($esportfilters ?? null) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-talents>
@endsection
