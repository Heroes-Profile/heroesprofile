@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <compositions-stats :filters="{{ json_encode($filters) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" :defaulttimeframe="{{ json_encode($defaulttimeframe) }}"></compositions-stats>
@endsection