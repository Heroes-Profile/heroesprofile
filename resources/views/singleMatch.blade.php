@extends('layouts.app', $bladeGlobals)    
@section('title', $esport ? $esport == 'Other' ? $series . ' Match ' . $replayID : $esport . ' Match ' . $replayID : 'Match ' . $replayID)
@section('meta_keywords', 'match ID, replay, single match, match details, replayID')
@section('meta_description', 'View detailed information about match ID ' . $replayID . ' on Heroes Profile. Explore match details, player statistics, and more.')
@section('content')
<single-match 
    :esport="{{ json_encode($esport) }}" 
    :series="{{ isset($series) ? json_encode($series) : 'null' }}" 
    :seriesimage="{{ isset($seriesimage) ? json_encode($seriesimage) : 'null' }}" 
    :replayid="{{ $replayID }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :user="{{ json_encode(Auth::user()) }}"
    :tournament="{{ json_encode($tournament) }}"
    :replay-viewer-allowed="{{ json_encode($replayViewerAllowed ?? false) }}"
    :analytics-cutover="{{ json_encode($analyticsCutover ?? null) }}"
>
</single-match>
@endsection
