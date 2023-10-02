@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <player-hero-single-stats 
    :filters="{{ json_encode($filters) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ $region }}" 
    :hero="{{ json_encode($hero) }}" 
    :regionsmap="{{ json_encode(session('regions')) }}"
    :heroobject="{{ json_encode($heroObject) }}"
  ></player-hero-single-stats>
@endsection
