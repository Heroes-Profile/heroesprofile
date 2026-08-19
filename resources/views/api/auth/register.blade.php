@extends('layouts.api')

@section('title', 'Register')
@section('meta_description', 'Create a Heroes Profile API account.')

@section('content')
  <api-register
    :csrf="{{ json_encode(csrf_token()) }}"
    :errors="{{ json_encode($errors->all()) }}"
    :oldname="{{ json_encode(old('name')) }}"
    :oldemail="{{ json_encode(old('email')) }}"
  >
  </api-register>
@endsection
