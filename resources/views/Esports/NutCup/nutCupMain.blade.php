@extends('layouts.app', $regions)  

@section('title', 'Nut Cup Esports')
@section('meta_keywords', 'Nut Cup league, Heroes of the Storm, Nut Cup esports, competitive gaming, Heroes of the Storm league, Nut Cup schedule')
@section('meta_description', 'Overall stats for Nut Cup')

@section('content')
  <nut-cup-main 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :heroes="{{ json_encode($heroes) }}" 
  >
  </nut-cup-main>
@endsection
