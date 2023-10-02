@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <global-matchups-talents-stats :heroes="{{ json_encode(session('heroes')) }}" :filters="{{ json_encode($filters) }}" :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" :inputhero="{{ json_encode($inputhero) }}" :inputenemyally="{{ json_encode($inputenemyally) }}" :advancedfiltering="{{ json_encode($advancedfiltering) }}"></global-matchups-talents-stats>
@endsection