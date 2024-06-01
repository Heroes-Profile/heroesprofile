@extends('layouts.app', $bladeGlobals)    
@section('title', $battletag . " " . $role . " Stats")
@section('meta_keywords', 'Player Role Stats, Role Statistics, Player Stats')
@section('meta_description', 'Explore the statistics and data for ' . $battletag . "'s performance in the " . $role . " role. Analyze player performance and stats for the specified role.")
@section('content')
  <player-role-single-stats 
    :filters="{{ json_encode($filters) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ $region }}" 
    :role="{{ json_encode($role) }}" 
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-role-single-stats>
@endsection
