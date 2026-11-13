@extends('layouts.app', $bladeGlobals)

@section('title', __(':hero Abilities, Talents & Stats', ['hero' => $heroname]))
@section('meta_description', $metadescription)
@section('meta_keywords', 'heroes of the storm ' . $heroname . ', hots ' . $heroname . ', ' . $heroname . ' abilities, ' . $heroname . ' talents, ' . $heroname . ' stats, ' . $heroname . ' tooltips')
@if ($ogimage)
  @section('og_image', $ogimage)
@endif
@section('content')
  <hero-page
    :herodata="{{ json_encode($herodata) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"
  >
  </hero-page>
@endsection
