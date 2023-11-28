@extends('layouts.app')
@section('title', 'Search Players')
@section('meta_keywords', 'heroes profile, search players, player search, player data')
@section('meta_description', 'Search for players and explore player data on Heroes Profile. Find information about heroes, talents, win rates, and more.')
@section('content')
  <searched-battletag-holding 
    :userinput="{{ json_encode($userinput) }}" 
    :type="{{ json_encode($type) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </searched-battletag-holding>
@endsection
