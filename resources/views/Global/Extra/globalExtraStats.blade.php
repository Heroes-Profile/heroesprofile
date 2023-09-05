@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <global-extra-stats :filters="{{ json_encode($filters) }}"></global-extra-stats>
@endsection