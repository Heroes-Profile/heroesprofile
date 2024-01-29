@extends('layouts.app', $bladeGlobals)    
@section('title', 'Compare Players and Leagues')
@section('meta_keywords', 'compare players, compare leagues, player comparison, league comparison')
@section('meta_description', 'Compare one or more players and/or leagues to analyze their performance, statistics, and achievements on Heroes Profile.')
@section('content')
  <compare 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :heroes="{{ json_encode($heroes) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </compare>
@endsection
