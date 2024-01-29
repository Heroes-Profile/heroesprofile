@extends('layouts.app', $bladeGlobals)    

@if ($userinput)
  @section('title', $userinput["name"] . ' Global Talent Builder')
@else
  @section('title', 'Global Talent Builder')
@endif


@section('meta_keywords', 'Talent Builder, Talent Builds, Hero Talents, Custom Builds')
@section('meta_description', 'Build and customize your own hero talent builds. Analyze talent performance with real data to optimize your hero builds for success.')
@section('content')
  <global-talents-builder 
    :heroes="{{ json_encode($heroes) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :filters="{{ json_encode($filters) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :talentimages="{{ json_encode($talentimages) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </global-talents-builder>
@endsection
