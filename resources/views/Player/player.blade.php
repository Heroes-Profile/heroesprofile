@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <player-stats 
    :settinghero="{{ json_encode($settingHero) }}" 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :filters="{{ json_encode($filters) }}"
    :season="{{ json_encode($season) }}"
    :gametype="{{ json_encode($game_type) }}"
    :regionsmap="{{ json_encode(session('regions')) }}"
  ></player-stats>
@endsection
