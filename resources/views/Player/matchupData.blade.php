@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <player-matchup :filters="{{ json_encode($filters) }}" :battletag="{{ json_encode($battletag) }}" :blizzid="{{ json_encode($blizz_id) }}" :region="{{ json_encode($region) }}"></player-matchup>
@endsection
