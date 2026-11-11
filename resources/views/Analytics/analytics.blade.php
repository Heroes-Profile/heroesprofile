@extends('layouts.app', $bladeGlobals)
@section('title', 'Analytics')
@section('meta_keywords', 'analytics, replay analysis, coaching')
@section('meta_description', 'Replay analytics for your own season games.')
@section('content')
  <analytics-page
    :battletag="{{ json_encode($battletag) }}"
    :initial-status="{{ json_encode($status) }}"
    :filters="{{ json_encode($filters) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :maps="{{ json_encode($maps) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"
    :playermatchtablestyle="{{ json_encode($playermatchtablestyle) }}"
    :sample="{{ json_encode($sample ?? null) }}"
    :initial-tab="{{ json_encode($initialTab ?? null) }}"
    :initial-match="{{ json_encode($initialMatch ?? null) }}"
    :trial-ends-at="{{ json_encode($trialEndsAt ?? null) }}"
    :trial-stage="{{ json_encode($trialStage ?? null) }}"
    :locked="{{ json_encode($locked ?? false) }}"
    :patreon-linked="{{ json_encode($patreonLinked ?? false) }}"
  >
  </analytics-page>
@endsection
