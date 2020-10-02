{{-- Some Pages do not use all of these filters.  So will need to make it dependant on some input --}}

<form id="basic_search" class="search-menu">
  {{-- Major Minor Picker --}}
  <select name="timeframe" class="selectpicker" multiple title="Minor" data-header="Timeframe Type">
    <option>Major</option>
    <option selected>Minor</option>
  </select>

  {{-- Major Game Version Picker --}}
  <select name="major_timeframe" class="selectpicker" multiple data-max-options="3" title={{ max(array_keys(getFilterVersions())) }} data-header="Major Timeframes">
    @foreach (getFilterVersions() as $major => $minor)
        <option value='{{ $major }}'>{{ $major }}</option>
    @endforeach
  </select>

  {{-- Minor Game Version Picker --}}
  <select name="minor_timeframe" class="selectpicker" multiple data-max-options="20" data-header="Minor Timeframes">
    @foreach (getFilterVersions() as $major => $minor)
      <optgroup label={{ $major }}>
      @for ($i = 0; $i < count($minor); $i++)
        @if ($minor[$i] == max(getAllMinorPatches()))
          <option value='{{ $minor[$i] }}' selected>{{ $minor[$i] }}</option>
        @else
          <option value='{{ $minor[$i] }}'>{{ $minor[$i] }}</option>
        @endif
      @endfor
    </optgroup>
    @endforeach
  </select>

  {{-- Region Picker --}}
  <select name="region" class="selectpicker" multiple title="All Regions" data-header="Regions">
    <option value='1'>NA</option>
    <option value='2'>EU</option>
    <option value='3'>KR</option>
    <option value='5'>CN</option>
  </select>

  @if($filtertype != "drafter")
    {{-- Stat Type Picker --}}
    <select name="stat_type" class="selectpicker" multiple title="Win Rate" data-header="Stats">
      @foreach (getScoreStatsByGrouping() as $grouping => $grouping_data)
        <optgroup label={{ $grouping }}>
        @for ($i = 0; $i < count($grouping_data); $i++)
          <option value='need to fix'>{{ $grouping_data[$i] }}</option>
        @endfor
      </optgroup>
      @endforeach
    </select>
  @endif


  {{-- Hero Level Picker --}}
  <select name="hero_level" class="selectpicker" multiple data-max-options="10" title="All Hero Levels" data-header="Hero Levels">
    <option value='1'>1-5</option>
    <option value='5'>5-10</option>
    <option value='10'>10-15</option>
    <option value='15'>15-20</option>
    <option value='20'>20-25</option>
    <option value='25'>25-40</option>
    <option value='40'>40-60</option>
    <option value='60'>60-80</option>
    <option value='80'>80-100</option>
    <option value='100'>100+</option>
  </select>

  @if($roleFilter)
    {{-- Roles Picker --}}
    <select name="role" class="selectpicker" multiple data-live-search="true" title="All Roles" data-header="Roles">
      @foreach (\App\Models\Hero::select("new_role as role")->distinct("role")->orderBy('role', 'ASC')->get() as $major => $minor)
          <option>{{ $minor->role }}</option>
      @endforeach
    </select>
  @endif


  @if($heroFilter)
    {{-- Heroes Picker --}}
    <select name="hero" class="selectpicker" multiple data-live-search="true" title="All Heroes" data-header="Heroes">
      @foreach (\App\Models\Hero::select('id', 'name')->orderBy('name', 'ASC')->get() as $major => $hero_data)
        @if($hero_data->name == $hero)
          <option value='{{ $hero_data->id }}' selected>{{ $hero_data->name }}</option>
        @else
          <option value='{{ $hero_data->id }}'>{{ $hero_data->name }}</option>
        @endif
      @endforeach
    </select>
  @endif

  {{-- Game Type Picker --}}
  <select name="game_type" class="selectpicker" multiple data-max-options="10" data-header="Game Types">
    <option value='1'>Quick Match</option>
    <option value='2'>Unranked Draft</option>
    <option value='5' selected>Storm League</option>
    <option value='-1'>Brawl</option>
  </select>


  {{-- Maps Picker --}}
  <select name="game_map" class="selectpicker" multiple data-live-search="true" title="All Maps" data-header="Maps">
    @foreach (getFilterMaps() as $section => $map_data)
      <optgroup label={{ $section }}>
      @for ($i = 0; $i < count($map_data); $i++)
        <option value='{{ $map_data[$i]["map_id"] }}'>{{ $map_data[$i]["name"] }}</option>
      @endfor
    </optgroup>
    @endforeach
  </select>


  {{-- Player Rank Picker --}}
  <select name="player_rank" class="selectpicker" multiple data-max-options="10" title="All Player Ranks" data-header="Player Ranks">
    <option value='6'>Master</option>
    <option value='5'>Diamond</option>
    <option value='4'>Platinum</option>
    <option value='3'>Gold</option>
    <option value='2'>Silver</option>
    <option value='1'>Bronze</option>
  </select>

  {{-- Hero Rank Picker --}}
  <select name="hero_rank" class="selectpicker" multiple data-max-options="10" title="All Hero Ranks" data-header="Hero Ranks">
    <option value='6'>Master</option>
    <option value='5'>Diamond</option>
    <option value='4'>Platinum</option>
    <option value='3'>Gold</option>
    <option value='2'>Silver</option>
    <option value='1'>Bronze</option>
  </select>

  {{-- Role Rank Picker --}}
  <select name="role_rank" class="selectpicker" multiple data-max-options="10" title="All Role Ranks" data-header="Role Ranks">
    <option value='6'>Master</option>
    <option value='5'>Diamond</option>
    <option value='4'>Platinum</option>
    <option value='3'>Gold</option>
    <option value='2'>Silver</option>
    <option value='1'>Bronze</option>
  </select>
  <input type="submit" value="update" class="btn btn-secondary"/>
</form>
