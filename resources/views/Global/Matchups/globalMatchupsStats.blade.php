@extends('layouts.app')

@if ($userinput)
  @section('title', $userinput["name"] . ' Global Hero Matchup Stats')
@else
  @section('title', 'Global Hero Matchup Stats')
@endif


@section('meta_keywords', 'Hero Matchup Stats, Hero Performance, Hero Counters')
@section('meta_description', 'Explore hero matchup statistics to see which heroes perform well against others. Analyze hero counters and matchups to improve your gameplay.')

@section('content')
  <global-matchups-stats :heroes="{{ json_encode(session('heroes')) }}" :inputhero="{{ json_encode($userinput)}}" :filters="{{ json_encode($filters) }}" :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :defaultbuildtype="{{ json_encode($defaultbuildtype) }}" :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" :advancedfiltering="{{ json_encode($advancedfiltering) }}"></global-matchups-stats>
@endsection