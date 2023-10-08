@extends('layouts.app')
@section('title', 'Home')
@section('meta_keywords', 'heroes profile, home page, hero statistics, player data')
@section('meta_description', 'Explore hero statistics, player data, and more on Heroes Profile, your source for in-depth insights into the Heroes of the Storm community.')
@section('content')
  <main-page :user="{{ json_encode(Auth::user()) }}"></main-page>
@endsection
