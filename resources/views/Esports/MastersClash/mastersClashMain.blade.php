@extends('layouts.app', $bladeGlobals)    

@section('title', __('Masters Clash Stats – Heroes of the Storm'))
@section('meta_keywords', 'Masters Clash league, Masters Clash, Heroes of the Storm, Masters Clash esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Heroes of the Storm statistics and comparison for the Masters Clash: hero stats, teams, players and match history.')

@section('content')
  <masters-clash-main 
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :urlparameters="{{ json_encode($urlparameters) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  >
  </maters-clash-main>
  @include('Esports.contactSection')
@endsection
