@extends('layouts.app', $bladeGlobals)
@section('title', $poll['title'])
@section('meta_keywords', 'poll, community')
@section('meta_description', 'Heroes Profile community poll: ' . $poll['title'])
@section('content')
  <community-poll
    :poll="{{ json_encode($poll) }}"
    :logged-in="{{ json_encode($loggedIn) }}"
    :initial-vote="{{ json_encode($myVote) }}"
    :initial-results="{{ json_encode($results) }}"
  >
  </community-poll>
@endsection
