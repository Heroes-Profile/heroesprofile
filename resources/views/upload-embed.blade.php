@extends('layouts.embed')
@section('title', 'Upload Replays')
@section('content')
  <replay-uploader-embed
    :upload-url="'{{ $uploadUrl }}'"
    :max-bytes="{{ $maxBytes }}"
    :void-stage="{{ json_encode($voidStage) }}"
  ></replay-uploader-embed>
@endsection
