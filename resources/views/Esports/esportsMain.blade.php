@extends('layouts.app', $bladeGlobals)    

@section('title', __('Heroes of the Storm Esports Stats'))
@section('meta_keywords', 'Heroes Profile, esports hub, competitive gaming, Heroes of the Storm esports, esports leagues, esports tournaments')
@section('meta_description', 'Heroes of the Storm esports stats for NGS, CCL, Masters Clash, Heroes International, Nut Cup, HGC and more: hero stats, divisions, teams, players and match history for every league.')

@section('content')
  <esports-main></esports-main>
@endsection
