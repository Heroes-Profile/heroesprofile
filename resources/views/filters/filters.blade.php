<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

{{-- Some Pages do not use all of these filters.  So will need to make it dependant on some input --}}

<form id="basic_search">
  {{-- Major Minor Picker --}}
  <select name="timeframe_type" class="selectpicker" multiple title="Minor" data-header="Timeframe Type">
    <option>Major</option>
    <option>Minor</option>
  </select>

  {{-- Major Game Version Picker --}}
  <select name="timeframe" class="selectpicker" multiple data-max-options="3" title={{ max(array_keys(getFilterVersions())) }} data-header="Major Timeframes">
    @foreach (getFilterVersions() as $major => $minor)
        <option>{{ $major }}</option>
    @endforeach
  </select>

  {{-- Minor Game Version Picker --}}
  <select name="timeframe" class="selectpicker" multiple data-max-options="20" title={{ max(getAllMinorPatches()) }} data-header="Minor Timeframes">
    @foreach (getFilterVersions() as $major => $minor)
      <optgroup label={{ $major }}>
      @for ($i = 0; $i < count($minor); $i++)
        <option>{{ $minor[$i] }}</option>
      @endfor
    </optgroup>
    @endforeach
  </select>

  {{-- Region Picker --}}
  <select name="region" class="selectpicker" multiple title="All Regions" data-header="Regions">
    <option>NA</option>
    <option>EU</option>
    <option>KR</option>
    <option>CN</option>
  </select>


  {{-- Stat Type Picker --}}
  <select name="stat_type" class="selectpicker" multiple title="Win Rate" data-header="Stats">
    @foreach (getScoreStatsByGrouping() as $grouping => $grouping_data)
      <optgroup label={{ $grouping }}>
      @for ($i = 0; $i < count($grouping_data); $i++)
        <option>{{ $grouping_data[$i] }}</option>
      @endfor
    </optgroup>
    @endforeach
  </select>

  {{-- Hero Level Picker --}}
  <select name="hero_level" class="selectpicker" multiple data-max-options="10" title="All Hero Levels" data-header="Hero Levels">
    <option>1-5</option>
    <option>5-10</option>
    <option>10-15</option>
    <option>15-20</option>
    <option>20-25</option>
    <option>25-40</option>
    <option>40-60</option>
    <option>60-80</option>
    <option>80-100</option>
    <option>100+</option>
  </select>


  {{-- Roles Picker --}}
  <select name="role" class="selectpicker" multiple data-live-search="true" title="All Roles" data-header="Roles">
    @foreach (\App\Models\Hero::select(DB::raw("DISTINCT(new_role) as role"))->orderBy('role', 'ASC')->get() as $major => $minor)
        <option>{{ $minor->role }}</option>
    @endforeach
  </select>

  {{-- Heroes Picker --}}
  <select name="hero" class="selectpicker" multiple data-live-search="true" title="All Heroes" data-header="Heroes">
    @foreach (\App\Models\Hero::select('name')->orderBy('name', 'ASC')->get() as $major => $minor)
        <option>{{ $minor->name }}</option>
    @endforeach
  </select>


  {{-- Game Type Picker --}}
  <select name="game_type" class="selectpicker" multiple data-max-options="10" title="Storm League" data-header="Game Types">
    <option>Quick Match</option>
    <option>Unranked Draft</option>
    <option>Storm League</option>
    <option>Brawl</option>
  </select>


  {{-- Maps Picker --}}
  <select name="game_map" class="selectpicker" multiple data-live-search="true" title="All Maps" data-header="Maps">
    @foreach (getFilterMaps() as $section => $map_data)
      <optgroup label={{ $section }}>
      @for ($i = 0; $i < count($map_data); $i++)
        <option>{{ $map_data[$i] }}</option>
      @endfor
    </optgroup>
    @endforeach
  </select>


  {{-- Player Rank Picker --}}
  <select name="player_rank" class="selectpicker" multiple data-max-options="10" title="All Player Ranks" data-header="Player Ranks">
    <option>Master</option>
    <option>Diamond</option>
    <option>Platinum</option>
    <option>Gold</option>
    <option>Silver</option>
    <option>Bronze</option>
  </select>

  {{-- Hero Rank Picker --}}
  <select name="hero_rank" class="selectpicker" multiple data-max-options="10" title="All Hero Ranks" data-header="Hero Ranks">
    <option>Master</option>
    <option>Diamond</option>
    <option>Platinum</option>
    <option>Gold</option>
    <option>Silver</option>
    <option>Bronze</option>
  </select>

  {{-- Role Rank Picker --}}
  <select name="role_rank" class="selectpicker" multiple data-max-options="10" title="All Role Ranks" data-header="Role Ranks">
    <option>Master</option>
    <option>Diamond</option>
    <option>Platinum</option>
    <option>Gold</option>
    <option>Silver</option>
    <option>Bronze</option>
  </select>
</form>
