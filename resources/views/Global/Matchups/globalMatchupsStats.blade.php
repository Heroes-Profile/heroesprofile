@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <global-matchups-stats :heroes="{{ json_encode(session('heroes')) }}" :inputhero="{{ json_encode($userinput)}}"></global-matchups-stats>
@endsection