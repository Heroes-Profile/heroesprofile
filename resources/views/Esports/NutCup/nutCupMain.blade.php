@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <nut-cup-main 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
    :heroes="{{ json_encode(session('heroes')) }}" 
  >
  </nut-cup-main>
@endsection
