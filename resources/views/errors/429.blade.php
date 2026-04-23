@php
    $bladeGlobals = [
        'regions' => [
            1 => 'NA',
            2 => 'EU',
            3 => 'KR',
            /* 4 => 'UNK', */
            5 => 'CN',
        ],
        'darkmode' => 0,
    ];
@endphp

@extends('layouts.app', $bladeGlobals)

@section('title', 'Too Many Requests')
@section('meta_keywords', '429 error, too many requests, rate limited, throttled request')
@section('meta_description', 'Too many requests were made in a short amount of time. Please wait and try again.')

@section('content')
    <div class="error-page">
        <div class="text-center">
            <h1>Too many requests</h1>
            <p>You are sending requests too quickly. Please wait a moment and try again.</p>
            <p>If this keeps happening, please submit a report at <a class="link" href="https://github.com/Heroes-Profile/heroesprofile/issues" target="_blank">https://github.com/Heroes-Profile/heroesprofile/issues</a>.</p>
        </div>

        <div class="flex justify-center items-center text-center bg-gray-100">
            <a href="/"><img src="/images/miscellaneous/500.png" alt="Rate limited"></a>
        </div>
    </div>
@endsection
