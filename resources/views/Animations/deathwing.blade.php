@extends('layouts.app', $bladeGlobals)  
@section('title', 'Deathwing Animation')
@section('meta_keywords', '')
@section('meta_description', '')
@section('content')
<div class="test-animation-page">
    <div class="deathwing-wrapper" style="width:100%; height: 300px; overflow:hidden;">
        <div id="wFkEdtPc" style="position: relative; height: 100%;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="{{ asset('Animations/Deathwing/assets/slplayer.js') }}"></script>
<script>
var deathwingJS = "1500-1800";

function reloadDeathwing() {
    if (window.innerWidth < 786) {
        deathwingJS = "667-768";
    }
    else if (window.innerWidth < 1024) {
        deathwingJS = "768-1024";
    }
    else if (window.innerWidth < 1500) {
        deathwingJS = "1280-1500";
    }
    else if (window.innerWidth < 1800) {
        deathwingJS = "1500-1800";
    }
    else if (window.innerWidth < 2200) {
        deathwingJS = "1800-2200";
    }
    else if (window.innerWidth < 2500) {
        deathwingJS = "2200-2500";
    }
    else if (window.innerWidth < 3000) {
        deathwingJS = "2500-3000";
    }

    (function(Saola) {
        var li = {
            "color": "#2090e6",
            "density": 9,
            "diameter": 60,
            "range": 1,
            "shape": "oval",
            "speed": 1,
            "resourceFolder": "{{ asset('Animations/Deathwing/assets/resources') }}"
        };
        Saola.openDoc("{{ asset('Animations/Deathwing/assets/Deathwing_') }}" + deathwingJS + ".js", 'wFkEdtPc', {paused: false, preloaderOptions: li, center: 'horizontal'});
    })(AtomiSaola);
}

$(window).resize(function() {
    reloadDeathwing();
});

$(document).ready(function() {
    reloadDeathwing();
});
</script>
@endpush

@push('styles')
<style>
.deathwing-wrapper img {
    display: none!important;
}

@media only screen and (max-width: 800px) {
    .deathwing-wrapper {
        margin-top: 54px;
        margin-bottom: -54px;
    }
}
</style>
@endpush