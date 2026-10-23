@extends('layouts.app', $bladeGlobals)
@section('title', 'Analytics')
@section('meta_keywords', 'analytics, replay analysis, coaching')
@section('meta_description', 'Replay analytics for your own season games, for Patreon supporters while in testing.')
@section('content')
  <analytics-info
    :logged-in="{{ json_encode($loggedIn) }}"
    :patreon-linked="{{ json_encode($patreonLinked) }}"
  >
  </analytics-info>
@endsection
