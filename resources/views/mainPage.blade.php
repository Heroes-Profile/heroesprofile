@extends('layouts.app', $bladeGlobals)  
@section('title', __('Heroes of the Storm Stats, Builds & Tier List'))
@section('footer_gap', '')
@section('own_bottom_ad', '1')
@section('meta_keywords', 'heroes of the storm profile, heroes of the storm statistics, heroes profile, hero statistics, player data, hots stats, heroes of the storm stats, win rates, pick rates')
@section('meta_description', 'Heroes of the Storm stats from millions of uploaded replays. Hero tier list, talent builds, counters, player profiles, MMR and match history on Heroes Profile.')
@section('content')
  <main-page 
    :user="{{ json_encode(Auth::user()) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :maxreplayid="{{ json_encode($maxReplayID) }}"
    :latestpatch="{{ json_encode($latestPatch) }}"
    :latestgamedate="{{ json_encode($latestGameDate) }}"
    :replay-ticker="{{ json_encode($replayTicker) }}"
    :parsing-stale-minutes="{{ \App\Services\GlobalDataService::PARSING_STALE_MINUTES }}"
    :analytics-allowed="{{ json_encode(Auth::check() && app(\App\Services\AnalyticsService::class)->isAllowed(Auth::user())) }}"
  >
  </main-page>
@endsection
