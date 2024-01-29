@extends('layouts.app', $bladeGlobals)    

@section('title', 'Global Composition Stats')
@section('meta_keywords', 'Heroes Profile, Composition Stats, Hero Roles, Hero Compositions, Win Rates, Compositions Data')
@section('meta_description', 'Explore global composition stats on Heroes Profile. Analyze hero roles and compositions, view win rates, and discover the most effective hero combinations.  Filter and analyze hero data to make informed decisions.')

@section('content')
  <compositions-stats 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </compositions-stats>
@endsection
