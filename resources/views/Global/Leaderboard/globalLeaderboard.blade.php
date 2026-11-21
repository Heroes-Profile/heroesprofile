@extends('layouts.app', $bladeGlobals)    

@section('title', __('Heroes of the Storm Leaderboards'))
@section('meta_keywords', 'heroes of the storm leaderboard, hots leaderboard, heroes of the storm rankings, hero leaderboards, player leaderboards, role leaderboards, stack size leaderboards')
@section('meta_description', 'Heroes of the Storm leaderboards from uploaded games: the top players overall, by hero and by role, real in-game Storm League ranks, and Match Prediction, by season and region.')

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
