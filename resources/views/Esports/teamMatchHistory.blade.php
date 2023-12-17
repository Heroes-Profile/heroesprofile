@extends('layouts.app', $regions)  
@section('title', 'Team & Organization Esports Match History Data')
@section('meta_keywords', 'Team & Organization Match History')
@section('meta_description', 'Team & Organization Match History')

@section('content')
  <esports-match-history 
    :esport="{{ json_encode($esport) }}" 
    :division="{{ json_encode($division) }}" 
    :team="{{ json_encode($team) }}" 
    :season="{{ json_encode($season) }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :type="{{ json_encode($type) }}" 
  ></esports-match-history>
@endsection
