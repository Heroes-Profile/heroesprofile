@extends('layouts.app', $bladeGlobals)
@php($regionName = $bladeGlobals['regions'][$region] ?? $region)
@include('Player.structuredData', ['page' => 'Match Awards'])
@section('title', __(':battletag (:region) Match Awards', ['battletag' => $battletag, 'region' => $regionName]))
@section('meta_keywords', 'Match Awards, MVP, Player Awards, Player Statistics')
@section('meta_description', 'How often ' . $battletag . ' (' . $regionName . ') earns MVP and every other end of match award in Heroes of the Storm.')
@section('content')
  <player-awards
    :battletag="{{ json_encode($battletag) }}"
    :playerloadsetting="{{ json_encode($playerloadsetting) }}"
    :blizzid="{{ json_encode($blizz_id) }}"
    :region="{{ json_encode($region) }}"
    :filters="{{ json_encode($filters) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :urlparameters="{{ json_encode((object) request()->query()) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  ></player-awards>
@endsection
