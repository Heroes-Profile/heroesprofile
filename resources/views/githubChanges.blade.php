@extends('layouts.app', $bladeGlobals)    
@section('title', 'Github Changes')
@section('meta_keywords', 'Github Changes')
@section('meta_description', 'Github Changes')
@section('content')
  <github-changes 
    :user="{{ json_encode(Auth::user()) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :access_token="{{ json_encode($access_token) }}"
  >
  </github-changes >
@endsection
