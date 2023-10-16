@extends('layouts.app')
@section('title', 'Match ' . $replayID)
@section('meta_keywords', 'game ID, replay, single match, match details, replayID')
@section('meta_description', 'View detailed information about match ID ' . $replayID . ' on Heroes Profile. Explore match details, player statistics, and more.')
@section('content')
  <single-match :esport="{{ json_encode($esport) }}" :replayid="{{ $replayID }}"></single-match>
@endsection
