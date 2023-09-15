@extends('layouts.app')
@section('title', 'Heroes Profile')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <searched-battletag-holding :userinput="{{ json_encode($userinput) }}" :type="{{ json_encode($type) }}"></searched-battletag-holding>
@endsection
