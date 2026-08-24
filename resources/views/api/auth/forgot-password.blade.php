@extends('layouts.api')

@section('title', 'Forgot Password')

@section('content')
  <api-forgot-password
    :csrf="{{ json_encode(csrf_token()) }}"
    :errors="{{ json_encode($errors->all()) }}"
    :status="{{ json_encode(session('status')) }}"
    :oldemail="{{ json_encode(old('email')) }}"
  >
  </api-forgot-password>
@endsection
