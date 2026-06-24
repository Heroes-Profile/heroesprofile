@extends('layouts.app', $bladeGlobals)

@section('title', 'Activity Graphs')

@section('meta_keywords', 'heroes of the storm activity, hots player count, heroes profile stats, monthly players, heroes of the storm graph')
@section('meta_description', 'Heroes of the Storm activity graphs showing unique player counts over time based on replays uploaded to Heroes Profile.')

@section('content')
  <activity-graphs
    :filters="{{ json_encode($filters) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </activity-graphs>
@endsection
