@extends('layouts.app', $bladeGlobals)

@section('title', 'Tassadar Animation')
@section('meta_keywords', '')
@section('meta_description', '')
@section('content')
<div class="test-animation-page">
    <div class="Tassadar-wrapper" style="width:100%; height: 300px; overflow:hidden;">
        <div id="wFkEdtPc" style="position: relative; height: 100%;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="{{ asset('Animations/Tassadar/assets/slplayer.js') }}"></script>
<script>
var TassadarJS = "1500-1800";

function reloadTassadar() {
    if (window.innerWidth < 786) {
        TassadarJS = "667-768";
    }
    else if (window.innerWidth < 1024) {
        TassadarJS = "768-1024";
    }
    else if (window.innerWidth < 1500) {
        TassadarJS = "1280-1500";
    }
    else if (window.innerWidth < 1800) {
        TassadarJS = "1500-1800";
    }
    else if (window.innerWidth < 2200) {
        TassadarJS = "1800-2200";
    }
    else if (window.innerWidth < 2500) {
        TassadarJS = "2200-2500";
    }
    else if (window.innerWidth < 3000) {
        TassadarJS = "2500-3000";
    }

    (function(Saola) {
        var li = {
            "color": "#2090e6",
            "density": 9,
            "diameter": 60,
            "range": 1,
            "shape": "oval",
            "speed": 1,
            "resourceFolder": "{{ asset('Animations/Tassadar/assets/resources') }}"
        };
        Saola.openDoc("{{ asset('Animations/Tassadar/assets/Tassadar_') }}" + TassadarJS + ".js", 'wFkEdtPc', {paused: false, preloaderOptions: li, center: 'horizontal'});
    })(AtomiSaola);
}

$(window).resize(function() {
    reloadTassadar();
});

$(document).ready(function() {
    reloadTassadar();
});
</script>
@endpush

@push('styles')
<style>
.Tassadar-wrapper img {
    display: none!important;
}

@media only screen and (max-width: 800px) {
    .Tassadar-wrapper {
        margin-top: 54px;
        margin-bottom: -54px;
    }
}
</style>
@endpush
