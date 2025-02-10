@extends('layouts.app', $bladeGlobals)    

@section('title', 'HGC and More Esports')
@section('meta_keywords', 'HGC and More esports, Heroes of the Storm, HGC and More league, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Stay up to date with the latest news and updates from the HGC and More Esports league. Review competitive Heroes of the Storm matches and follow your favorite teams.')

@section('content')
  <other-main 
    :heroes="{{ json_encode($heroes) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :series="{{ json_encode($series) }}"
  >
  </other-main>
@endsection
