@extends('layouts.app', $bladeGlobals)
@section('title', $battletag . "'s Awards")
@section('meta_keywords', 'Match Awards, MVP, Player Awards, Player Statistics')
@section('meta_description', 'Explore the match awards of ' . $battletag . ', including how often they earn MVP and every other end of match award.')
@section('content')
  <player-awards
    :battletag="{{ json_encode($battletag) }}"
    :playerloadsetting="{{ json_encode($playerloadsetting) }}"
    :blizzid="{{ json_encode($blizz_id) }}"
    :region="{{ json_encode($region) }}"
    :filters="{{ json_encode($filters) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  ></player-awards>
@endsection
