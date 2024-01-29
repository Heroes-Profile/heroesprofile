@extends('layouts.app', $bladeGlobals)    

@section('title', 'Team & Organization Esports Data')
@section('meta_keywords', 'Heroes Profile, team data, organization data, esports data, team performance, organization performance, Heroes of the Storm')
@section('meta_description', 'Explore esports data for specific teams and organizations on Heroes Profile. Analyze team and organization performance in various esports leagues, track their statistics, and view their achievements in Heroes of the Storm.')

@section('content')
  <esports-single-team 
    :esport="{{ json_encode($esport) }}" 
    :division="{{ json_encode($division) }}" 
    :team="{{ json_encode($team) }}" 
    :season="{{ json_encode($season) }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :image="{{ json_encode($image) }}" 
  ></esports-single-team>
@endsection
