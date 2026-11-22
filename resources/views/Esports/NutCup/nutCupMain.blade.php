@extends('layouts.app', $bladeGlobals)    

@section('title', __('Nut Cup Stats – Heroes of the Storm'))
@section('meta_keywords', 'Nut Cup, Nut Cup hero stats, Nut Cup talent builds, Heroes of the Storm esports, Heroes of the Storm tournament')
@section('meta_description', 'Heroes of the Storm hero and talent stats from the Nut Cup: win rates, popularity, bans and talent builds.')

@section('content')
  <nut-cup-main 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :urlparameters="{{ json_encode($urlparameters) }}"
    :heroes="{{ json_encode($heroes) }}" 
  >
  </nut-cup-main>
  @include('Esports.contactSection')
@endsection
