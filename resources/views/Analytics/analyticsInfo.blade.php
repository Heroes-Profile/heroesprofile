@extends('layouts.app', $bladeGlobals)
@section('title', 'Analytics')
@section('meta_keywords', 'analytics, replay analysis, coaching')
@section('meta_description', 'Replay analytics for your own season games. Try it free for 7 days.')
@section('content')
  <analytics-info
    :logged-in="{{ json_encode($loggedIn) }}"
    :patreon-linked="{{ json_encode($patreonLinked) }}"
    :trial-available="{{ json_encode($trialAvailable ?? false) }}"
    :trial-ended="{{ json_encode($trialEnded ?? false) }}"
    :trial-days="{{ json_encode($trialDays ?? 7) }}"
    :free-games="{{ json_encode($freeGames ?? 100) }}"
    :trial-stage="{{ json_encode($trialStage ?? null) }}"
  >
  </analytics-info>
@endsection
