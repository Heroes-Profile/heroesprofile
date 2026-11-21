@extends('layouts.app', $bladeGlobals)    
@section('title', __(':team Esports Match History', ['team' => $team]))
@section('meta_keywords', 'Team & Organization Match History')
@section('meta_description', 'Heroes of the Storm esports match history for ' . $team . ': every game with heroes, maps and results.')

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
