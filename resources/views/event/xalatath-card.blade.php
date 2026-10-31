@extends('layouts.app', $bladeGlobals)
@section('title', "{$name}'s Void Corruption Card")
@section('meta_keywords', "Xal'atath, Void Corruption, Heroes of the Storm, Heroes Profile")
@section('meta_description', "How much {$name} fed the Void during the Xal'atath event on Heroes Profile.")
@section('og_image', $imageUrl)
@section('content')
  <div class="max-w-[1200px] mx-auto px-4 py-8 text-center text-white">
    <h1 class="font-logo text-3xl md:text-5xl xalatath-title mb-6">{{ $name }}'s Void Corruption</h1>
    <img src="{{ $imageUrl }}" alt="{{ $name }}'s Xal'atath card" class="w-full h-auto rounded mx-auto" />
    <a href="/Event/Xalatath" class="inline-block mt-6 bg-[#5b21b6] hover:bg-[#7b3fe4] text-white px-6 py-3 rounded">See the full Xal'atath recap</a>
  </div>
@endsection
