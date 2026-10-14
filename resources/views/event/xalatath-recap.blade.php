@extends('layouts.app', $bladeGlobals)
@section('title', "Xal'atath Event Recap")
@section('meta_keywords', "Xal'atath, Void Corruption, Heroes of the Storm event recap, Xal'atath stats")
@section('meta_description', "Everything Xal'atath left behind: final Void Corruption totals, records, talents, matchups and the most corrupted players on Heroes Profile.")
@section('og_image', asset('images/event/xalatath/xalatath-logo-stage-'.($recap['final']['stage'] ?? 5).'.png'))
@section('content')
  <xalatath-recap :recap="{{ json_encode($recap) }}" :contribution="{{ json_encode($contribution) }}"></xalatath-recap>
@endsection
