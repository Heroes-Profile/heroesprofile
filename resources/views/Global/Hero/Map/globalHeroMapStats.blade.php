@extends('layouts.app')

@section('title', 'Global Hero-Map Stats')
@section('meta_keywords', 'Hero Map Stats, Hero Performance, Map Synergy, Map Picks, Map Win Rates')
@section('meta_description', 'Explore global hero-map statistics to discover hero performance on various maps, map synergy, hero picks, and win rates. Filter and analyze hero data to make informed decisions.')

@section('content')
  <global-hero-map-stats :heroes="{{ json_encode(session('heroes')) }}"  :filters="{{ json_encode($filters) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :inputhero="{{ json_encode($userinput)}}" :defaulttimeframe="{{ json_encode($defaulttimeframe)
