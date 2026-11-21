@extends('layouts.app', $bladeGlobals)
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => 'Party Win Rates'])
@section('title', __(':battletag (:region) Party Win Rates', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Party Data, Party Size, Stack Win Rates, Player Statistics')
@section('meta_description', 'Heroes of the Storm win rates for ' . $battletag . ' (' . $regionName . ') by party setup, comparing the groups on their team against the groups they played against.')
@section('content')
  <player-party
    :filters="{{ json_encode($filters) }}"
    :playerloadsetting="{{ json_encode($playerloadsetting) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :battletag="{{ json_encode($battletag) }}"
    :blizzid="{{ json_encode($blizz_id) }}"
    :region="{{ json_encode($region) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"
  ></player-party>
@endsection
