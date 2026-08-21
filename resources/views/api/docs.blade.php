@extends('layouts.api')

@section('title', 'Docs')
@section('meta_description', 'Every endpoint in the Heroes Profile API — parameters, response shapes and quota keys.')

@section('content')
  <api-docs
    :spec="{{ json_encode($spec) }}"
    :authenticated="{{ json_encode($authenticated) }}"
  ></api-docs>
@endsection
