@extends('layouts.app', $bladeGlobals)    

@section('title', __('Heroes of the Storm Talent Matchups'))
@section('meta_keywords', 'heroes of the storm talent matchups, hots talent matchups, talent matchup stats, hero talents, hero matchups, talent performance')
@section('meta_description', 'Heroes of the Storm talent win rates and popularity for a hero with or against another hero, so you can see which talents work best in each matchup.')

@section('content')
  <global-matchups-talents-stats
    :heroes="{{ json_encode($heroes) }}"
    :filters="{{ json_encode($filters) }}"
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}"
    :inputhero="{{ json_encode($inputhero) }}"
    :inputenemyally="{{ json_encode($inputenemyally) }}"
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"

  >
  </global-matchups-talents-stats>
@endsection
