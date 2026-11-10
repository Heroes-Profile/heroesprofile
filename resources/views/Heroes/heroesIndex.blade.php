@extends('layouts.app', $bladeGlobals)

@section('title', 'Heroes: Abilities, Talents & Stats')
@section('meta_description', 'Every Heroes of the Storm hero with ability and talent tooltips and base stats from the current patch, scaled to any level from 1 to 30, on Heroes Profile.')
@section('meta_keywords', 'heroes of the storm heroes, hots heroes, hots hero list, hero abilities, hero talents, hero stats, talent tooltips')
@section('content')
  <heroes-index
    :heroes="{{ json_encode($heroes) }}"
  >
  </heroes-index>
@endsection
