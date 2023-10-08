@extends('layouts.app')
@section('title', $userinput . ' Talent Stats & Builds')
@section('meta_keywords', 'Talent Stats, Talent Win Rates, Talent Builds, Hero Talents')
@section('meta_description', 'Explore talent stats for heroes, including talent win rates and talent builds. Analyze which talents perform well and customize your hero builds for success.')
@section('content')
  <global-talents-stats 
    :heroes="{{ json_encode(session('heroes')) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :filters="{{ json_encode($filters) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :talentimages="{{ json_encode($talentimages) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
  >
  </global-talents-stats>
@endsection
