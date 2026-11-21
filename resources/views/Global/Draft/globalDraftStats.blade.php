@extends('layouts.app', $bladeGlobals)    

@if ($userinput)
  @section('title', __(':hero Draft Stats & Pick Order', ['hero' => $userinput["name"]]))
  @section('meta_description', 'When to pick or ban ' . $userinput["name"] . ' in Heroes of the Storm drafts. Pick rate, ban rate and win rate at every draft position in Storm League.')
@else
  @section('title', __('Heroes of the Storm Draft Stats & Pick Order'))
  @section('meta_description', 'Storm League pick rates, ban rates and pick order for every hero in Heroes of the Storm drafts. Pick a hero to see when it is picked or banned at each draft position.')
@endif

@section('meta_keywords', 'heroes of the storm draft, hots draft, draft stats, hero bans, hero picks, draft order, drafting data, heroes of the storm drafting')

@section('content')
  <global-draft-stats 
    :heroes="{{ json_encode($heroes) }}" 
    :inputhero="{{ json_encode($userinput)}}" 
    :filters="{{ json_encode($filters) }}" 
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}" 
    :gametypedefault="{{ json_encode($gametypedefault) }}" 
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" 
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"

  >
  </global-draft-stats>
@endsection
