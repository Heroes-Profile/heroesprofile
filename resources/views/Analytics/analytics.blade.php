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
  >
  </analytics-page>
@endsection
