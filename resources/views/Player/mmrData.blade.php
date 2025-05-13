@extends('layouts.app', $bladeGlobals)    
@section('title', $battletag . "'s HP MMR Data")
@section('meta_keywords', 'HP MMR Data, Matchmaking Rating, Player Skill, Skill Rating, Player Statistics')
@section('meta_description', 'Explore the HP MMR data of ' . $battletag . ', including match making rating, player skill, and player statistics.')
@section('content')
  <mmr-data 
    :battletag="{{ json_encode($battletag) }}" 
    :playerloadsetting="{{ json_encode($playerloadsetting) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :filters="{{ json_encode($filters) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :regionsmap="{{ json_encode($bladeGlobals['regions']) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  ></mmr-data>
@endsection
