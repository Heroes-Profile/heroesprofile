@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <player-roles-all-stats :filters="{{ json_encode($filters) }}" :battletag="{{ json_encode($battletag) }}" :blizzid="{{ json_encode($blizz_id) }}" :region="{{ json_encode($region) }}"></player-roles-all-stats>
@endsection
