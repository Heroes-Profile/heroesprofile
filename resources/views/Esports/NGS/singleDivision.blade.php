@extends('layouts.app')

@section('title', 'NGS Divisions Esport')
@section('meta_keywords', 'Heroes Profile, NGS, Divisions, Heroes of the Storm, NGS divisions, competitive gaming, esports, division performance')
@section('meta_description', 'Explore data for divisions in NGS (Nexus Gaming Series) on Heroes Profile. Analyze division performance, view division statistics, and track their achievements in Heroes of the Storm competitive gaming.')

@section('content')
  <ngs-single-division
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :division="{{ json_encode($division) }}"
    :season="{{ json_encode($season) }}"
  >
  </ngs-single-division>
@endsection
