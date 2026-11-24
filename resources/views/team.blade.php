@extends('layouts.app', $bladeGlobals)
@section('title', __('Team'))
@section('meta_keywords', 'Heroes Profile team, Heroes Profile staff, Skill Tree Development, Heroes of the Storm stats')
@section('meta_description', 'The people behind Heroes Profile, the Heroes of the Storm statistics site run by Skill Tree Development, LLC since June 2018.')
@section('content')
  <team-page :members="{{ json_encode($members) }}"></team-page>
@endsection
