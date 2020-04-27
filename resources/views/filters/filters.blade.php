<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

{{-- Some Pages do not use all of these filters.  So will need to make it dependant on some input --}}



{{-- Major Minor Picker --}}
<select class="selectpicker" multiple title="Minor">
  <option>Major</option>
  <option>Minor</option>
</select>

{{-- Major Game Version Picker --}}
<select class="selectpicker" multiple multiple data-max-options="10" multiple title={{ max(array_keys(getFilterVersions())) }}>
  @foreach (getFilterVersions() as $major => $minor)
      <option>{{ $major }}</option>
  @endforeach
</select>

{{-- Minor Game Version Picker --}}
<select class="selectpicker" multiple multiple data-max-options="10" multiple title={{ max(getAllMinorPatches()) }}>
  @foreach (getFilterVersions() as $major => $minor)
    <optgroup label={{ $major }}>
    @for ($i = 0; $i < count($minor); $i++)
      <option>{{ $minor[$i] }}</option>
    @endfor
  </optgroup>
  @endforeach
</select>

{{-- Region Picker --}}
<select class="selectpicker" multiple multiple data-max-options="10">
  <option>NA</option>
  <option>EU</option>
  <option>KR</option>
  <option>CN</option>
</select>


{{-- Stat Type Picker.  Likely need to make this an object --}}
<select class="selectpicker" data-live-search="true" multiple title="Win Rate">
  <option value="win_rate">Win Rate</option>
  <option value='game_time'>Game Time</option>
  <option value='kills'>Kills</option>
  <option value='takedowns'>Takedowns</option>
  <option value='deaths'>Deaths</option>
  <option value='siege_damage'>Siege Damage</option>
  <option value='hero_damage'>Hero Damage</option>
  <option value='healing'>Healing</option>
  <option value='damage_taken'>Damage Taken</option>
  <option value='experience_contribution'>Experience Contribution</option>
  <option value='assists'>Assists</option>
  <option value='highest_kill_streak'>Highest Kill Streak</option>
  <option value='structure_damage'>Structure Damage</option>
  <option value='minion_damage'>Minion Damage</option>
  <option value='creep_damage'>Lane Merc. Damage</option>
  <option value='summon_damage'>Summon Damage</option>
  <option value='self_healing'>Self Healing</option>
  <option value='town_kills'>Town Kills</option>
  <option value='time_spent_dead'>Time Spent Dead</option>
  <option value='merc_camp_captures'>Merc Camp Captures</option>
  <option value='watch_tower_captures'>Watch Tower Captures</option>
  <option value='protection_Allies'>Protection Allies</option>
  <option value='silencing_enemies'>Silencing Enemies</option>
  <option value='rooting_enemies'>Rooting Enemies</option>
  <option value='stunning_enemies'>Stunning Enemies</option>
  <option value='clutch_heals'>Clutch Heals</option>
  <option value='escapes'>Escapes</option>
  <option value='vengeance'>Vengeance</option>
  <option value='outnumbered_deaths'>Outnumbered Deaths</option>
  <option value='teamfight_escapes'>Teamfight Escapes</option>
  <option value='teamfight_healing'>Teamfight Healing</option>
  <option value='teamfight_damage_taken'>Teamfight Damage Taken</option>
  <option value='teamfight_hero_damage'>Teamfight Hero Damage</option>
  <option value='multikill'>Multikill</option>
  <option value='physical_damage'>Physical Damage</option>
  <option value='spell_damage'>Spell Damage</option>
  <option value='regen_globes'>Regen Globes</option>
</select>

{{-- Hero Level Picker --}}
<select class="selectpicker" multiple multiple data-max-options="10">
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
<select class="selectpicker" multiple data-live-search="true">
  @foreach (\App\Models\Hero::select(DB::raw("DISTINCT(new_role) as role"))->orderBy('role', 'ASC')->get() as $major => $minor)
      <option>{{ $minor->role }}</option>
  @endforeach
</select>

{{-- Heroes Picker --}}
<select class="selectpicker" multiple data-live-search="true">
  @foreach (\App\Models\Hero::select('name')->orderBy('name', 'ASC')->get() as $major => $minor)
      <option>{{ $minor->name }}</option>
  @endforeach
</select>


{{-- Game Type Picker --}}
<select class="selectpicker" multiple multiple data-max-options="10" multiple title="Storm League">
  <option>Quick Match</option>
  <option>Unranked Draft</option>
  <option>Storm League</option>
  <option>Brawl</option>
</select>


{{-- Maps Picker --}}
<select class="selectpicker" multiple>
  @foreach (getFilterMaps() as $section => $map_data)
    <optgroup label={{ $section }}>
    @for ($i = 0; $i < count($map_data); $i++)
      <option>{{ $map_data[$i] }}</option>
    @endfor
  </optgroup>
  @endforeach
</select>


{{-- Player Rank Picker --}}
<select class="selectpicker" multiple multiple data-max-options="10">
  <option>Master</option>
  <option>Diamond</option>
  <option>Platinum</option>
  <option>Gold</option>
  <option>Silver</option>
  <option>Bronze</option>
</select>

{{-- Hero Rank Picker --}}
<select class="selectpicker" multiple multiple data-max-options="10">
  <option>Master</option>
  <option>Diamond</option>
  <option>Platinum</option>
  <option>Gold</option>
  <option>Silver</option>
  <option>Bronze</option>
</select>

{{-- Role Rank Picker --}}
<select class="selectpicker" multiple multiple data-max-options="10">
  <option>Master</option>
  <option>Diamond</option>
  <option>Platinum</option>
  <option>Gold</option>
  <option>Silver</option>
  <option>Bronze</option>
</select>

<script>
  $('select').selectpicker();
</script>
