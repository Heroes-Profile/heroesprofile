@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <player-talents 
    :battletag="{{ json_encode($battletag) }}" 
    :blizzid="{{ json_encode($blizz_id) }}" 
    :region="{{ json_encode($region) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :filters="{{ json_encode($filters) }}"
    :heroes="{{ json_encode(session('heroes')) }}"
    :talentimages="{{ json_encode($talentimages) }}"  
    :regionsmap="{{ json_encode(session('regions')) }}"
    >
    </player-talents>
@endsection
