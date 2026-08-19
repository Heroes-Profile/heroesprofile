@extends('layouts.api')

@section('title', 'Reset Password')

@section('content')
  <api-reset-password
    :csrf="{{ json_encode(csrf_token()) }}"
    :token="{{ json_encode($token) }}"
    :email="{{ json_encode($email) }}"
    :errors="{{ json_encode($errors->all()) }}"
  >
  </api-reset-password>
@endsection
