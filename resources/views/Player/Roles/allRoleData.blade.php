@extends('layouts.app', $bladeGlobals)    
@section('title', __(':battletag All Role Stats', ['battletag' => $battletag]))
@section('meta_keywords', 'Player Role Stats, Role Statistics, Player Stats')
@section('meta_description', 'Explore the statistics and data for all roles played by ' . $battletag . '. Analyze player performance and stats for different roles.')
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
