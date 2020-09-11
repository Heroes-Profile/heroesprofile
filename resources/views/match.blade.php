@extends('layouts.app')
@section('title', 'Match Single Page')

@section('content')
  @include('nav.profile')
  <div class="container">
    {{ print_r(json_encode($replay, true)) }}
  </div>
@endsection

@section('scripts')
@endsection
