@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <main-page :user="{{ json_encode(Auth::user()) }}"></main-page>
@endsection
