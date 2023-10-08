@extends('layouts.app')
@section('title', 'Talent Builder')
@section('meta_keywords', 'Talent Builder, Talent Builds, Hero Talents, Custom Builds')
@section('meta_description', 'Build and customize your own hero talent builds. Analyze talent performance with real data to optimize your hero builds for success.')
@section('content')
  <global-talents-builder 
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
  </global-talents-builder>
@endsection
