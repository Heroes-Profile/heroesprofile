@extends('layouts.app', $bladeGlobals)    
@section('title', 'Team & Organization Esports Match History Data')
@section('meta_keywords', 'Team & Organization Match History')
@section('meta_description', 'Team & Organization Match History')

@section('content')
  <esports-match-history 
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :division="{{ json_encode($division) }}" 
    :team="{{ json_encode($team) }}" 
    :season="{{ json_encode($season) }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :type="{{ json_encode($type) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></esports-match-history>
@endsection
