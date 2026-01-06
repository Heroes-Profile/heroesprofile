@extends('layouts.app', $bladeGlobals)  
@section('title', 'Home')
@section('meta_keywords', 'heroes of the storm profile, heroes of the storm statistics, heroes profile, hero statistics, player data, hots stats, heroes of the storm stats, win rates, pick rates')
@section('meta_description', 'Heroes Profile - Your Heroes of the Storm profile and statistics hub. Explore hero win rates, pick rates, ban rates, player statistics, match history, and comprehensive Heroes of the Storm data.')
@section('content')
  <main-page 
    :user="{{ json_encode(Auth::user()) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :maxreplayid="{{ json_encode($maxReplayID) }}"
    :latestpatch="{{ json_encode($latestPatch) }}"
    :latestgamedate="{{ json_encode($latestGameDate) }}"
  >
  </main-page>
@endsection
