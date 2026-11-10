@extends('layouts.embed')
@section('title', 'Upload Replays')
@section('content')
  <replay-uploader-embed
    :upload-url="'{{ $uploadUrl }}'"
    :max-bytes="{{ $maxBytes }}"
  ></replay-uploader-embed>
@endsection
