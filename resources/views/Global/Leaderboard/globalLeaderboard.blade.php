@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <global-leaderboard :filters="{{ json_encode($filters) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :defaultseason="{{ json_encode($defaultseason) }}" :advancedfiltering="{{ json_encode($advancedfiltering) }}"></global-leaderboard>
@endsection