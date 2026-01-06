@extends('layouts.app', $bladeGlobals)    

@section('title', 'Global Talent Matchup Stats')
@section('meta_keywords', 'heroes of the storm talent matchups, hots talent matchups, talent matchup stats, hero talents, hero matchups, talent performance')
@section('meta_description', 'Heroes of the Storm talent matchup statistics. Explore talent matchup stats between heroes, both as allies and opponents. Analyze which talents work well together and customize your hero builds for success on Heroes Profile.')

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
