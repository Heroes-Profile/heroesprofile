@extends('layouts.app')
@section('title', 'Global Party Stats')
@section('meta_keywords', 'Party Stats, Hero Stacks, Stack Size, Stack Performance')
@section('meta_description', 'Explore party stats, including stack size and performance. Analyze how different stack sizes fare against others.')

@section('content')
  <global-party-stats 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </global-party-stats>
@endsection