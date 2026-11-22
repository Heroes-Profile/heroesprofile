@php
    $bladeGlobals = [
        'regions' => [
            1 => 'NA',
            2 => 'EU',
            3 => 'KR',
            /* 4 => 'UNK', */
            5 => 'CN',
        ],
        'darkmode' => request()->cookie('darkmode') === '1' ? 1 : 0,
        'heroes' => [],
        'maps' => [],
    ];
    // The visitor's theme and the footer's patch and replay count, as on other pages. Left at the cookie / out of the footer if the lookup fails
    try {
        $globalData = app(\App\Services\GlobalDataService::class);
        $bladeGlobals['darkmode'] = (int) $globalData->getDarkMode();
        $bladeGlobals['maxReplayID'] = $globalData->calculateMaxReplayNumber();
        $bladeGlobals['latestPatch'] = $globalData->getLatestPatch();
    } catch (\Throwable $e) {
    }
@endphp

@extends('layouts.app', $bladeGlobals)
@section('no_ads', '1')

@section('title', __('Too Many Requests'))
@section('meta_keywords', '429 error, too many requests, rate limited, throttled request')
@section('meta_description', 'Too many requests were made in a short amount of time. Please wait and try again.')

@section('content')
    <div class="error-page">
        <div class="text-center">
            <h1>{{ __('Too many requests') }}</h1>
            <p>{{ __('You are sending requests too quickly. Please wait a moment and try again.') }}</p>
            <p>{{ __('If this keeps happening, please submit a report at') }} <a class="link" href="https://github.com/Heroes-Profile/heroesprofile/issues" target="_blank">https://github.com/Heroes-Profile/heroesprofile/issues</a>.</p>
        </div>

        <div class="flex justify-center items-center text-center">
            <a href="/"><img src="/images/miscellaneous/500.png" alt="Rate limited"></a>
        </div>
    </div>
@endsection
