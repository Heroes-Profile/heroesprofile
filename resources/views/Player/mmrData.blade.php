@extends('layouts.app')
@section('title', $battletag . "'s MMR Data")
@section('meta_keywords', 'MMR Data, Matchmaking Rating, Player Skill, Skill Rating, Player Statistics')
@section('meta_description', 'Explore the MMR data of ' . $battletag . ', including match making rating, player skill, and player statistics.')
@section('content')
  <mmr-data 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :regionsmap="{{ json_encode(session('regions')) }}"
    :is-patreon="{{ json_encode($patreon) }}"
  ></mmr-data>
@endsection
