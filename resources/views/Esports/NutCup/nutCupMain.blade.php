@extends('layouts.app', $bladeGlobals)    

@section('title', __('Nut Cup Esports'))
@section('meta_keywords', 'Nut Cup league, Heroes of the Storm, Nut Cup esports, competitive gaming, Heroes of the Storm league, Nut Cup schedule')
@section('meta_description', 'Heroes of the Storm statistics and comparison for the Nut Cup: overall hero stats, teams, players and match history.')

@section('content')
  <nut-cup-main 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :urlparameters="{{ json_encode($urlparameters) }}"
    :heroes="{{ json_encode($heroes) }}" 
  >
  </nut-cup-main>
@endsection
