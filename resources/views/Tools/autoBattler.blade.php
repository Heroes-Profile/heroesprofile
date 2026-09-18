@extends('layouts.app', $bladeGlobals)

@section('title', 'Auto Battler')

@section('meta_keywords', 'heroes of the storm auto battler, hots ai talent builds, ai drafter, hots ai vs ai, heroes profile')
@section('meta_description', 'Pick two teams of Heroes of the Storm heroes and talent builds, then download the AI build files to watch them battle in the Veteran Introduction.')

@section('content')
  <auto-battler
    :heroes="{{ json_encode($heroes) }}"
    :talentbuilderstyle="{{ json_encode($talentbuilderstyle) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </auto-battler>
@endsection
