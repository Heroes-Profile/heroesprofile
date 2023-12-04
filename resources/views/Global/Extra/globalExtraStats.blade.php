@extends('layouts.app', $regions)  
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <global-extra-stats 
    :filters="{{ json_encode($filters) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </global-extra-stats>
@endsection