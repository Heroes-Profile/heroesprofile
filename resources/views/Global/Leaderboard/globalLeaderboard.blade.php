@extends('layouts.app', $bladeGlobals)    

@section('title', 'Global Leaderboards')
@section('meta_keywords', 'Hero Leaderboards, Player Leaderboards, Role Leaderboards, Stack Size Leaderboards')
@section('meta_description', 'Explore global hero leaderboards, including player leaderboards, hero leaderboards, role leaderboards, and leaderboards for different stack sizes. Compete to reach the top and showcase your hero mastery.')

@section('content')
  <global-leaderboard 
    :user="{{ json_encode(Auth::user()) }}" 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :defaultpredictionseason="{{ json_encode($defaultpredictionseason) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :weekssincestart="{{ json_encode($weekssincestart) }}"
    :matchpredictionweekssincestart="{{ json_encode($matchpredictionweekssincestart) }}"
  >
  </global-leaderboard>
@endsection
