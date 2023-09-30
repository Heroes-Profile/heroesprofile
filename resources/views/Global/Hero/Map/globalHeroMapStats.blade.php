@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <global-hero-map-stats :heroes="{{ json_encode(session('heroes')) }}"  :filters="{{ json_encode($filters) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :inputhero="{{ json_encode($userinput)}}" :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" :advancedfiltering="{{ json_encode($advancedfiltering) }}"></global-hero-map-stats>
@endsection