@extends('layouts.app', $bladeGlobals)

@section('title', 'Randomize Me')

@section('meta_keywords', 'heroes of the storm random hero, hots random build, randomize hero, random talent build, heroes profile')
@section('meta_description', 'Get a random Heroes of the Storm hero and a random talent build from real recent games. Use it as a fun challenge or to try something new.')

@section('content')
  <randomize-me
    :heroes="{{ json_encode($heroes) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </randomize-me>
@endsection
