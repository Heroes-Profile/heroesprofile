@extends('layouts.api')

@section('title', __('Register'))
@section('robots', 'noindex, nofollow')
@section('meta_description', __('Create a Heroes Profile API account.'))

@section('content')
  <api-register
    :csrf="{{ json_encode(csrf_token()) }}"
    :errors="{{ json_encode($errors->all()) }}"
    :oldname="{{ json_encode(old('name')) }}"
    :oldemail="{{ json_encode(old('email')) }}"
  >
  </api-register>
@endsection
