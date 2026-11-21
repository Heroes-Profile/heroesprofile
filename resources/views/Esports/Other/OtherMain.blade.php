@extends('layouts.app', $bladeGlobals)    

@section('title', __('HGC and More Esports'))
@section('meta_keywords', 'HGC and More esports, Heroes of the Storm, HGC and More league, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'HGC and other Heroes of the Storm esports tournaments, with a filterable list of replays for each tournament.')

@section('content')
  <other-main 
    :heroes="{{ json_encode($heroes) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :series="{{ json_encode($series) }}"
    :seasons="{{ json_encode($seasons) }}"
    :regions="{{ json_encode($regions) }}"
    :tournaments="{{ json_encode($tournaments) }}"
    :teams="{{ json_encode($teams) }}"
  >
  </other-main>
@endsection
