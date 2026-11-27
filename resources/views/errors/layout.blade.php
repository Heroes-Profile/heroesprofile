@php
    $bladeGlobals = [
        'regions' => [
            1 => 'NA',
            2 => 'EU',
            3 => 'KR',
            /* 4 => 'UNK', */
            5 => 'CN',
        ],
        'heroes' => [],
        'maps' => [],
    ];
    // The footer's patch and replay count, as on other pages. Left out of the footer if the lookup fails
    try {
        $globalData = app(\App\Services\GlobalDataService::class);
        $bladeGlobals['maxReplayID'] = $globalData->calculateMaxReplayNumber();
        $bladeGlobals['latestPatch'] = $globalData->getLatestPatch();
    } catch (\Throwable $e) {
    }
@endphp

{{-- The error card (ErrorCard.vue), filled by each error page; Popular pages unless a page has its own extra --}}
@extends('layouts.app', $bladeGlobals)
@section('no_ads', '1')

@section('content')
    <div class="max-w-[84rem] w-full mx-auto px-4 md:px-8 py-10 md:py-20">
        <error-card>
            <template #badge>@yield('error_badge')</template>
            <template #heading>@yield('error_heading')</template>
            <template #text>@yield('error_text')</template>
            <template #actions>@yield('error_actions')</template>
            @hasSection('error_extra')
                <template #extra>@yield('error_extra')</template>
            @endif
        </error-card>
    </div>
@endsection
