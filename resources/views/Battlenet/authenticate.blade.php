@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <battlenet-authenticate :filters="{{ json_encode($filters) }}"></battlenet-authenticate>
@endsection