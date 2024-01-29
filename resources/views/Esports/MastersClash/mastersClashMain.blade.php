@extends('layouts.app', $bladeGlobals)    

@section('title', 'Masters Clash Esports')
@section('meta_keywords', 'Masters Clash league, Masters Clash, Heroes of the Storm, Masters Clash esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Stay up to date with the latest news and updates from the Masters Clash league. Review competitive Heroes of the Storm matches and follow your favorite teams.')

@section('content')
  <masters-clash-main 
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
  >
  </maters-clash-main>
@endsection
