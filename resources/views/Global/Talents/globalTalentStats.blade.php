@extends('layouts.app', $bladeGlobals)    

@if ($userinput)
  @section('title', $userinput["name"] . ' Talent Stats & Builds')
@else
  @section('title', 'Talent Stats & Builds')
@endif

@section('meta_keywords', 'heroes of the storm talents, hots talents, talent stats, talent win rates, talent builds, hero talents, heroes of the storm builds')
@section('meta_description', 'Heroes of the Storm talent statistics and builds. Explore talent stats for heroes, including talent win rates and talent builds. Analyze which talents perform well and customize your hero builds for success on Heroes Profile.')
@section('content')
  <global-talents-stats 
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
    :urlparameters="{{ json_encode($urlparameters) }}"

  >
  </global-talents-stats>
@endsection
