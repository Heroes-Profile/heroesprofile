@extends('layouts.app', $bladeGlobals)    

@section('title', __('Heroes International Stats – Heroes of the Storm'))
@section('meta_keywords', 'Heroes International league, Heroes International, Heroes of the Storm, Heroes International esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Heroes of the Storm statistics for Heroes International: the main league and the Nations Cup, with hero stats, teams, players and match history.')

@section('content')
  <heroes-international-entry></heroes-international-entry>
@endsection
