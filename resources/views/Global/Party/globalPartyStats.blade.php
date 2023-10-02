@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <global-party-stats :filters="{{ json_encode($filters) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" :advancedfiltering="{{ json_encode($advancedfiltering) }}"></global-party-stats>
@endsection

