@extends('layouts.app')

@section('title', 'Talent Matchup Stats')
@section('meta_keywords', 'Talent Matchup Stats, Hero Talents, Hero Matchups, Talent Performance')
@section('meta_description', 'Explore talent matchup stats between heroes, both as allies and opponents. Analyze which talents work well together and customize your hero builds for success.')

@section('content')
  <global-matchups-talents-stats
    :heroes="{{ json_encode(session('heroes')) }}"
    :filters="{{ json_encode($filters) }}"
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}"
    :inputhero="{{ json_encode($inputhero) }}"
    :inputenemyally="{{ json_encode($inputenemyally) }}"
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
  ></global-matchups-talents-stats>
@endsection
