@extends('layouts.app', $bladeGlobals)
@section('title', __('Heroes Profile Change Log – Latest Site Updates'))
@section('meta_keywords', 'heroes profile changes, heroes profile updates, heroes profile change log, hots stats site updates, github commits')
@section('meta_description', 'Every recent change to Heroes Profile, the Heroes of the Storm stats site: the latest updates on the live site and the test site, straight from GitHub.')
@section('content')
  <github-changes
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :master-commits="{{ json_encode($masterCommits) }}"
    :develop-commits="{{ json_encode($developCommits) }}"
  >
  </github-changes >
@endsection
