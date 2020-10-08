
@if(count($controller_hero_data) > 0)
  @for($i = 0; $i < count($controller_hero_data); $i++)
    <div class="rounded-item {{ $controller_hero_data[$i]["starred"] }}"><a class="rounded-picture hero-picture"
    data-herorolename="{{ $controller_hero_data[$i]["new_role"] }}"
    data-heroname="{{ $controller_hero_data[$i]["name"] }}"
    data-heroid="{{ $controller_hero_data[$i]["id"] }}"
    data-heroimg="{{ asset('/images/heroes/' . $controller_hero_data[$i]["short_name"] . '.png') }}"
       role="button"
       data-html="true"
       data-toggle="popover"
       data-trigger="hover"
       data-placement="top"
       title="{{ $controller_hero_data[$i]["name"] }}"
       data-content="
         HP Draft Value: {{ $controller_hero_data[$i]["value"] }} <br>
         Games Played: {{ number_format($controller_hero_data[$i]["games_played"]) }} <br>
         Influence: {{ $controller_hero_data[$i]["influence"] }} <br>
       @if($bans)
         Ban Rate: {{ $controller_hero_data[$i]["ban_rate"] }}% <br>
       @endif
         Win Rate: {{ $controller_hero_data[$i]["win_rate"] }}% <br>
         Win Rate Confidence: {{ "&#177;" }} {{ $controller_hero_data[$i]["win_rate_confidence"] }}% <br>
         Draft Pick Order Rate: {{ $controller_hero_data[$i]["pick_order_percent"] }}%
       "
       >
        <img src="{{ asset('/images/heroes/' . $controller_hero_data[$i]["short_name"] . '.png') }}" data-heroimg="{{ asset('/images/heroes/' . $controller_hero_data[$i]["short_name"] . '.png') }}">
     </a>
   </div>
  @endfor
@endif
