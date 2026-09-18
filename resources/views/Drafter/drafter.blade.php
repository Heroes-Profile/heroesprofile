@extends('layouts.app', $bladeGlobals)

@section('title', 'Drafter')

@section('meta_keywords', 'heroes of the storm drafter, hots draft tool, hots draft helper, draft picks, draft bans, heroes profile drafter')
@section('meta_description', 'Heroes of the Storm draft helper. Get ban and pick suggestions at every step of a Storm League draft, based on draft order, win rates, and team compositions.')

@section('content')
  <drafter
    :heroes="{{ json_encode($heroes) }}"
    :filters="{{ json_encode($filters) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </drafter>
@endsection
