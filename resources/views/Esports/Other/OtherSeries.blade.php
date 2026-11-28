@extends('layouts.app', $bladeGlobals)    

@section('title', __(':series Stats – Heroes of the Storm', ['series' => optional($series)->name ?? 'HGC and More']))
@section('meta_keywords', 'HGC and More esports, Heroes of the Storm, HGC and More league, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Heroes of the Storm statistics and comparison for ' . (optional($series)->name ?? 'HGC and more') . ': hero stats, teams, players and replays.')

@section('content')
  <other-series
    :heroes="{{ json_encode($heroes) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :urlparameters="{{ json_encode($urlparameters) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :series="{{ json_encode($series) }}"
    :seasons="{{ json_encode($seasons) }}"
    :regions="{{ json_encode($regions) }}"
    :tournaments="{{ json_encode($tournaments) }}"
  >
  </other-series>
  @include('Esports.contactSection')
@endsection
