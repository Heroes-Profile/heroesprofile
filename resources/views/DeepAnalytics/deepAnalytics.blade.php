@extends('layouts.app', $bladeGlobals)
@section('title', 'Deep Analytics')
@section('meta_keywords', 'deep analytics, replay analysis, coaching')
@section('meta_description', 'Deep replay analytics for your own season games.')
@section('content')
  <deep-analytics-page
    :battletag="{{ json_encode($battletag) }}"
    :initial-status="{{ json_encode($status) }}"
  >
  </deep-analytics-page>
@endsection
