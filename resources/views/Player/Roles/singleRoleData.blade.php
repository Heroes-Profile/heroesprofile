@extends('layouts.app')
@section('title', $battletag . " " . $role . " Stats")
@section('meta_keywords', 'Player Role Stats, Role Statistics, Player Stats')
@section('meta_description', 'Explore the statistics and data for ' . $battletag . "'s performance in the " . $role . " role. Analyze player performance and stats for the specified role.")
@section('content')
  <player-role-single-stats 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ $region }}" 
    :role="{{ json_encode($role) }}" 
    :regionsmap="{{ json_encode(session('regions')) }}"
    :is-patreon="{{ json_encode($patreon) }}"
  ></player-role-single-stats>
@endsection
