@extends('layouts.api')

@section('title', __('Sign In'))
@section('robots', 'noindex, nofollow')
@section('meta_description', __('Sign in to your Heroes Profile API account.'))

@section('content')
  <api-login
    :csrf="{{ json_encode(csrf_token()) }}"
    :errors="{{ json_encode($errors->all()) }}"
    :status="{{ json_encode(session('status')) }}"
    :oldemail="{{ json_encode(old('email')) }}"
  >
  </api-login>
@endsection
