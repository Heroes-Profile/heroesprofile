@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <ngs-main :defaultseason="{{ json_encode($defaultseason) }}" :filters="{{ json_encode($filters) }}"></ngs-main>
@endsection
