@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <mmr-data :battletag="{{ json_encode($battletag) }}" :blizzid="{{ json_encode($blizz_id) }}" :region="{{ json_encode($region) }}" :filters="{{ json_encode($filters) }}" :gametypedefault="{{ json_encode($gametypedefault) }}"></mmr-data>
@endsection