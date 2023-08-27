@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <global-matchups-talents-stats :heroes="{{ json_encode(session('heroes')) }}"></global-matchups-talents-stats>
@endsection