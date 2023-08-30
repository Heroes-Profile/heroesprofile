@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <profile-settings :user="{{ json_encode(Auth::user()) }}"></profile-settings>
@endsection
