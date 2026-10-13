@extends('layouts.app', $bladeGlobals)
@section('title', 'Replay Viewer')
@section('meta_keywords', 'replay viewer, deep analytics')
@section('meta_description', 'Watch one of your games on the minimap.')
@section('content')
  <deep-analytics-replay :replay-id="{{ json_encode($replayId) }}"></deep-analytics-replay>
@endsection
