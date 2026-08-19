@extends('layouts.api')

@section('title', 'Account')

@section('content')
  <api-account :account="{{ json_encode($account) }}"></api-account>
@endsection
