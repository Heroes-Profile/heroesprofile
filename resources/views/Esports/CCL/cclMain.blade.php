@extends('layouts.app', $bladeGlobals)    

@section('title', 'CCL Esports')
@section('meta_keywords', 'CCL esports, Heroes of the Storm, CCL league, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Stay up to date with the latest news and updates from the CCL Esports league. Review competitive Heroes of the Storm matches and follow your favorite teams.')

@section('content')
  <ccl-main 
    :heroes="{{ json_encode($heroes) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
  >
  </ccl-main>
@endsection
