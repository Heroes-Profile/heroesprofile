<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

{{-- Some Pages do not use all of these filters.  So will need to make it dependant on some input --}}

<form id="basic_search">



  {{-- Type Picker --}}
  <select name="leaderboard_type" id="type-picker" class="selectpicker" title="Player" data-header="Leaderboard Type">
    <option value ='player' selected>Player</option>
    <option value='hero'>Hero</option>
    <option value='role'>Role</option>
  </select>

  {{-- Heroes Picker --}}
  <select name="hero" id="hero-picker" class="selectpicker" data-live-search="true" title="Hero" data-header="Heroes">
    @foreach (\App\Models\Hero::select('id', 'name')->orderBy('name', 'ASC')->get() as $major => $hero_data)
        <option value='{{ $hero_data->id }}'>{{ $hero_data->name }}</option>
    @endforeach
  </select>

  {{-- Roles Picker --}}
  <select name="role" id="role-picker" class="selectpicker" data-live-search="true" title="Role" data-header="Roles">
    @foreach (\App\Models\Hero::select(DB::raw("DISTINCT(new_role) as role"))->orderBy('role', 'ASC')->get() as $major => $minor)
        <option>{{ $minor->role }}</option>
    @endforeach
  </select>

  {{-- Game Type Picker --}}
  <select name="game_type" class="selectpicker" data-header="Game Types">
    <option value='1'>Quick Match</option>
    <option value='2'>Unranked Draft</option>
    <option value='5' selected>Storm League</option>
    <option value='-1'>Brawl</option>
  </select>


  {{-- Season Picker --}}
  <select name="season" class="selectpicker" data-header="Season">
    @foreach (\App\Models\SeasonDate::select('id', 'year', 'season')->orderBy('id', 'DESC')->where('id', '>=', '13')->get() as $data => $season_data)
        <option value='{{ $season_data->id }}'>{{ $season_data->year }}{{ ' Season ' }}{{ $season_data->season }}</option>
    @endforeach
  </select>


  {{-- Region Picker --}}
  <select name="region" class="selectpicker" title="All Regions" data-header="Regions">
    <option value='1'>NA</option>
    <option value='2'>EU</option>
    <option value='3'>KR</option>
    <option value='5'>CN</option>
  </select>
</form>

<script>
//$('.roles-selectpicker').selectpicker();
//$('.heroes-selectpicker').selectpicker();

// To style only selects with the my-select class
//$('.timeframe_type-selectpicker').selectpicker();
// To style all selects
//$('select').selectpicker();
</script>
