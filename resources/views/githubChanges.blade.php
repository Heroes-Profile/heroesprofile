@extends('layouts.app', $bladeGlobals)
@section('title', 'Github Changes')
@section('meta_keywords', 'Github Changes')
@section('meta_description', 'Github Changes')
@section('content')
  <github-changes
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :master-commits="{{ json_encode($masterCommits) }}"
    :develop-commits="{{ json_encode($developCommits) }}"
  >
  </github-changes >
@endsection
