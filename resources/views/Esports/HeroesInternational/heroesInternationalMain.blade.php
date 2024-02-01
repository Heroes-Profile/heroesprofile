@extends('layouts.app', $bladeGlobals)    

@section('title', 'Heroes International Esports')
@section('meta_keywords', 'Heroes International league, Heroes International, Heroes of the Storm, Heroes International esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Stay up to date with the latest news and updates from the Heroes International league. Review competitive Heroes of the Storm matches and follow your favorite teams.')

@section('content')
  <heroes-international-main
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  ></heroes-international-main>
@endsection
