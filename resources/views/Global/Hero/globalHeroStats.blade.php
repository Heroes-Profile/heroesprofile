@extends('layouts.app')

@section('title', 'Global Hero Stats')
@section('meta_keywords', 'Hero Win Rates, Pick Rate, Ban Rate, Hero Influence, Win Rate Confidence, Hero Performance')
@section('meta_description', 'Explore global hero win rate statistics, including pick rate, ban rate, hero influence, win rate confidence, and hero performance metrics. Filter and analyze hero data to make informed decisions.')

@section('content')
  <global-hero-stats 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    >
  </global-hero-stats>
@endsection
