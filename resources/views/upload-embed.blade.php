@extends('layouts.embed')
@section('title', __('Upload Replays'))
@section('content')
  <replay-uploader-embed
    :upload-url="'{{ $uploadUrl }}'"
    :max-bytes="{{ $maxBytes }}"
  ></replay-uploader-embed>
@endsection
