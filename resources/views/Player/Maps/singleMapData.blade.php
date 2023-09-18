@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <player-map-single-stats :filters="{{ json_encode($filters) }}" :battletag="{{ json_encode($battletag) }}" :blizzid="{{ json_encode($blizz_id) }}" :region="{{ $region }}" :map="{{ json_encode($map) }}" :regions="{{ json_encode($regions) }}"></player-map-single-stats>
@endsection
