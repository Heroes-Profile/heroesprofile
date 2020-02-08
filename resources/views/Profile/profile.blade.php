@extends ('layouts.app')
@section('content')

 <!--<profile-tab-switcher></profile-tab-switcher>-->
 <div class="stat-section">
   <stat-box title="Win Rates" :data='@json($winrates)' decimals=2 ></stat-box>
  <stat-box title="Win Rates" :data='@json($winrates)' decimals=2 ></stat-box>

</div>
<div class="stat-section">
    <stat-box title="Games Played" :data='@json($mostplayed)' decimals=0 ></stat-box>
      <stat-box title="Highest Win Rate" :data='@json($mostplayed)' decimals=0 ></stat-box>
        <stat-box title="Latest Played" :data='@json($mostplayed)' decimals=0 ></stat-box>
</div>
@endsection
