@extends('layouts.app', $bladeGlobals)  
@section('title', 'Home')
@section('meta_keywords', 'heroes profile, home page, hero statistics, player data')
@section('meta_description', 'Explore hero statistics, player data, and more on Heroes Profile, your source for in-depth insights into the Heroes of the Storm community.')
@section('content')
  <community-support
    :user="{{ json_encode(Auth::user()) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :maxreplayid="{{ json_encode($maxReplayID) }}"
    :latestpatch="{{ json_encode($latestPatch) }}"
    :latestgamedate="{{ json_encode($latestGameDate) }}"
    :patreonearnings="{{ json_encode($patreonEarnings) }}"
  >
  </community-support>
@endsection
