@extends('layouts.app', $bladeGlobals)    

@section('title', __('NGS Esports'))
@section('meta_keywords', 'NGS league, Nexus Gaming Series, Heroes of the Storm, NGS esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Stay up to date with the latest news and updates from the NGS (Nexus Gaming Series) league. Review competitive Heroes of the Storm matches and follow your favorite teams.')

@section('content')
  <ngs-main 
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :urlparameters="{{ json_encode($urlparameters) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  >
  </ngs-main>
@endsection
