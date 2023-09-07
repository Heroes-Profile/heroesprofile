@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <compare :filters="{{ json_encode($filters) }}" :gametypedefault="{{ json_encode($gametypedefault) }}"></compare>
@endsection
