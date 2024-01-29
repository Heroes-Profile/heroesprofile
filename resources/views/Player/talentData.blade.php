@extends('layouts.app', $bladeGlobals)    
@section('title', $battletag . "'s Talent Stats")
@section('meta_keywords', 'Player Talents, Talent Statistics, Talent Performance, Talent Builds, Talent Win Rate')
@section('meta_description', 'Explore the talent stats of ' . $battletag . ', including talent performance, talent builds, and win rate statistics.')
@section('content')
  <player-talents 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :filters="{{ json_encode($filters) }}"
    :heroes="{{ json_encode($heroes) }}"
    :talentimages="{{ json_encode($talentimages) }}"  
    :regionsmap="{{ json_encode($regions) }}"
    :is-patreon="{{ json_encode($patreon) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}" 

  ></player-talents>
@endsection
