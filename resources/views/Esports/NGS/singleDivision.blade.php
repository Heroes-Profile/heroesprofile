@extends('layouts.app', $bladeGlobals)    

@section('title', __('NGS :division Division Stats', ['division' => $division]))
@section('meta_keywords', 'Heroes Profile, NGS, Divisions, Heroes of the Storm, NGS divisions, competitive gaming, esports, division performance')
@section('meta_description', 'The NGS (Nexus Gaming Series) ' . $division . ' division in Heroes of the Storm: its teams, hero stats and matches.')

@section('content')
  <ngs-single-division
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :division="{{ json_encode($division) }}"
    :season="{{ json_encode($season) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  >
  </ngs-single-division>
@endsection
