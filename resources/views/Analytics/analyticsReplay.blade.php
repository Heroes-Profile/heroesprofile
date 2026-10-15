@extends('layouts.app', $bladeGlobals)
@section('title', 'Replay Viewer')
@section('meta_keywords', 'replay viewer, analytics')
@section('meta_description', 'Watch one of your games on the minimap.')
@section('content')
  <analytics-replay :replay-id="{{ json_encode($replayId) }}" :urlparameters="{{ json_encode($urlparameters) }}"></analytics-replay>
@endsection
