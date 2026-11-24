@extends('layouts.app', $bladeGlobals)    

{{-- A matchup's URL (/Abathur/Alarak) gets its own title and description --}}
@php($isMatchup = ($inputhero->name ?? 'Auto Select') !== 'Auto Select' && ($inputenemyally->name ?? 'Auto Select') !== 'Auto Select')
@section('title', $isMatchup ? $inputhero->name . ' vs ' . $inputenemyally->name . ' Talent Matchups' : 'Global Talent Matchup Stats')
@section('meta_keywords', 'heroes of the storm talent matchups, hots talent matchups, talent matchup stats, hero talents, hero matchups, talent performance')
@section('meta_description', $isMatchup ? 'Heroes of the Storm ' . $inputhero->name . ' talent win rates and popularity against or with ' . $inputenemyally->name . ', by patch, game mode, map and rank, so you can see which talents work best in that matchup.' : 'Heroes of the Storm talent matchup statistics. Explore talent matchup stats between heroes, both as allies and opponents. Analyze which talents work well together and customize your hero builds for success on Heroes Profile.')

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
