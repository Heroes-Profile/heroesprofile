<form id="basic_search">
  {{-- Game Type Picker --}}
  <select name="game_type" class="selectpicker" multiple title="All Game Types" data-header="Game Types">
    <option value='1'>Quick Match</option>
    <option value='2'>Unranked Draft</option>
    <option value='3'>Hero League</option>
    <option value='4'>Team League</option>
    <option value='5'>Storm League</option>
  </select>

  {{-- Season Picker --}}
  <select name="hero" class="selectpicker" multiple title="All Seasons" data-header="Seasons">
    @foreach (\App\Models\SeasonDate::select('id', 'year', 'season')->orderBy('id', 'DESC')->get() as $data => $season_data)
        <option value='{{ $season_data->id }}'>{{ $season_data->year }} Season {{ $season_data->season }}</option>
    @endforeach
  </select>

</form>
