@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <esports-player-hero-stats 
    :esport="{{ json_encode($esport) }}" 
    :division="{{ json_encode($division) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizz_id="{{ json_encode($blizz_id) }}" 
    :season="{{ json_encode($season) }}" 
    :hero="{{ json_encode($hero) }}" 
  ></esports-player-hero-stats>
@endsection
