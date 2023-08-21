@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <main-page></main-page>
@endsection


@section('max_replay_id', $maxReplayID)
@section('latest_path', $latestPatch)
@section('latest_game_in_timezone', $latestGameDate)

