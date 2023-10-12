@extends('layouts.app')

@if ($userinput)
  @section('title', $userinput["name"] . ' Global Draft Stats')
@else
  @section('title', 'Global Draft Stats')
@endif

@section('meta_keywords', 'Draft Stats, Hero Bans, Hero Picks, Draft Order, Drafting Data')
@section('meta_description', 'Explore global draft stats. Analyze hero bans, picks, draft order, and drafting data to strategize your games effectively. Filter and analyze hero data to make informed decisions.')

@section('content')
  <global-draft-stats :heroes="{{ json_encode(session('heroes')) }}" :inputhero="{{ json_encode($userinput)}}" :filters="{{ json_encode($filters) }}" :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" :advancedfiltering="{{ json_encode($advancedfiltering) }}"></global-draft-stats>
@endsection
