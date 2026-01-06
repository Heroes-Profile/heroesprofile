@extends('layouts.app', $bladeGlobals)    

@if ($userinput)
  @section('title', $userinput["name"] . ' Global Talent Builder')
@else
  @section('title', 'Global Talent Builder')
@endif


@section('meta_keywords', 'heroes of the storm talent builder, hots talent builder, talent builder, talent builds, hero talents, custom builds, heroes of the storm builds')
@section('meta_description', 'Heroes of the Storm talent builder and custom build creator. Build and customize your own hero talent builds. Analyze talent performance with real data to optimize your hero builds for success on Heroes Profile.')
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
    :urlparameters="{{ json_encode($urlparameters) }}"

  >
  </global-talents-builder>
@endsection
