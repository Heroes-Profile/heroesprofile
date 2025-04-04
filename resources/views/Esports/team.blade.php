@extends('layouts.app', $bladeGlobals)    

@section('title', 'Team & Organization Esports Data')
@section('meta_keywords', 'Heroes Profile, team data, organization data, esports data, team performance, organization performance, Heroes of the Storm')
@section('meta_description', 'Explore esports data for specific teams and organizations on Heroes Profile. Analyze team and organization performance in various esports leagues, track their statistics, and view their achievements in Heroes of the Storm.')

@section('content')
  <esports-single-team 
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :division="{{ json_encode($division) }}" 
    :team="{{ json_encode($team) }}" 
    :season="{{ json_encode($season) }}" 
    :region="{{ isset($reghion) ? json_encode($region) : 'null' }}" 
    :tournament="{{ json_encode($tournament) }}" 
    :image="{{ json_encode($image) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></esports-single-team>
@endsection
