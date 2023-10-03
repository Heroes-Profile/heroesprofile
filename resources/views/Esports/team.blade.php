@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <esports-single-team 
    :esport="{{ json_encode($esport) }}" 
    :divisions="{{ json_encode($divisions) }}" 
    :division="{{ json_encode($division) }}" 
    :team="{{ json_encode($team) }}" 
    :season="{{ json_encode($season) }}" 
  ></esports-single-team>
@endsection
