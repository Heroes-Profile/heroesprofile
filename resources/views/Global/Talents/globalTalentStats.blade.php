@extends('layouts.app', $bladeGlobals)

@if ($userinput)
  @section('title', $userinput["name"] . ' Builds & Talent Stats')
  @if ($topbuild && data_get($topbuild, 'games_played'))
    @section('meta_description', 'Best ' . $userinput["name"] . ' builds for Heroes of the Storm on patch ' . $defaulttimeframe[0] . '. The top build wins ' . data_get($topbuild, 'win_rate') . '% over ' . number_format(data_get($topbuild, 'games_played')) . ' games. Talent win rates and popularity at every level.')
  @else
    @section('meta_description', 'Best ' . $userinput["name"] . ' builds for Heroes of the Storm on patch ' . $defaulttimeframe[0] . '. Talent win rates and popularity at every level.')
  @endif
@else
  @section('title', 'Talent Stats & Builds')
  @section('meta_description', 'Heroes of the Storm talent statistics and builds. Explore talent stats for heroes, including talent win rates and talent builds. Analyze which talents perform well and customize your hero builds for success on Heroes Profile.')
@endif

@section('meta_keywords', 'heroes of the storm talents, hots talents, talent stats, talent win rates, talent builds, hero talents, heroes of the storm builds')
@section('content')
  <global-talents-stats
    :heroes="{{ json_encode($heroes) }}"
    :inputhero="{{ json_encode($userinput)}}"
    :filters="{{ json_encode($filters) }}"
    :defaulttimeframe="{{ json_encode($defaulttimeframe) }}"
    :gametypedefault="{{ json_encode($gametypedefault) }}"
    :defaultbuildtype="{{ json_encode($defaultbuildtype) }}"
    :talent-stats-layout="{{ json_encode($talentStatsLayout) }}"
    :authenticated="{{ json_encode(auth()->check()) }}"
    :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}"
    :talentimages="{{ json_encode($talentimages) }}"
    :advancedfiltering="{{ json_encode($advancedfiltering) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :urlparameters="{{ json_encode($urlparameters) }}"
    :initialtalentdata="{{ json_encode($initialtalentdata) }}"
    :initialbuilddata="{{ json_encode($initialbuilddata) }}"

  >
    {{-- Crawlers get the heading and builds in the HTML; Vue drops it on mount --}}
    <div v-pre class="sr-only">
      <h1 class="text-xl md:text-3xl">{{ $userinput ? $userinput["name"] . ' Builds & Talent Stats' : 'Hero Builds & Talent Stats' }}</h1>
      @if ($userinput && $initialbuilddata)
        <h2 class="heading mt-4 mb-2">Top {{ $userinput["name"] }} Builds, Patch {{ $defaulttimeframe[0] }}</h2>
        <ol class="text-sm">
          @foreach ($initialbuilddata as $build)
            <li class="mb-1">
              {{ collect(['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'])
                  ->map(fn ($level) => data_get($build, $level . '.title') ?: data_get($build, $level . '.talent_name'))
                  ->filter()
                  ->implode(' / ') }}
              - {{ data_get($build, 'win_rate') }}% win rate over {{ number_format(data_get($build, 'games_played')) }} games
            </li>
          @endforeach
        </ol>
      @endif
    </div>
  </global-talents-stats>
@endsection
