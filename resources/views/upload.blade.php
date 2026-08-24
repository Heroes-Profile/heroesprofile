@extends('layouts.app', $bladeGlobals)
@section('title', 'Upload Replays')
@section('meta_keywords', 'heroes of the storm replay upload, hots replay uploader, upload replays, heroes profile uploader')
@section('meta_description', 'Upload your Heroes of the Storm replays to Heroes Profile — from your browser, or automatically with the desktop uploader.')
@section('content')
  <replay-uploader
    :upload-url="'{{ $uploadUrl }}'"
    :max-bytes="{{ $maxBytes }}"
  ></replay-uploader>
@endsection
