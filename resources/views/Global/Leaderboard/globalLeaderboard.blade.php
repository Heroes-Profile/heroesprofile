@extends('layouts.app', $bladeGlobals)    

@section('title', 'Global Leaderboards')
@section('meta_keywords', 'heroes of the storm leaderboard, hots leaderboard, heroes of the storm rankings, hero leaderboards, player leaderboards, role leaderboards, stack size leaderboards')
@section('meta_description', 'Heroes of the Storm leaderboards and rankings. Explore global hero leaderboards, player leaderboards, role leaderboards, and leaderboards for different stack sizes. Compete to reach the top and showcase your hero mastery on Heroes Profile.')

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
    :urlparameters="{{ json_encode($urlparameters) }}"
  >
  </global-leaderboard>
@endsection
