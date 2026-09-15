@extends('layouts.app', $bladeGlobals)
@section('title', 'Replay Search')
@section('meta_keywords', 'Replay Search, Match Search, Heroes of the Storm Replays')
@section('meta_description', 'Search recent Heroes of the Storm replays by game type, map, heroes, players, patch and more.')
@section('content')
  <match-search
    :filters="{{ json_encode($filters) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :oldestdate="{{ json_encode($oldestdate) }}"
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  ></match-search>
@endsection
