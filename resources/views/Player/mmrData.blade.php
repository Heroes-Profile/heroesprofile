@extends('layouts.app', $bladeGlobals)    
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => 'MMR History'])
@section('title', __(':battletag (:region) MMR History', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'HP MMR Data, Matchmaking Rating, Player Skill, Skill Rating, Player Statistics')
@section('meta_description', 'Heroes of the Storm HP MMR for ' . $battletag . ' (' . $regionName . '): matchmaking rating over time for each game mode and role.')
@section('content')
  <mmr-data 
    :battletag="{{ json_encode($battletag) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  ></mmr-data>
@endsection
