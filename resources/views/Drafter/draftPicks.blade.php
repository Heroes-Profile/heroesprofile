
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
       data-content="Value: {{ $controller_hero_data[$i]["value"] }}">
        <img src="{{ asset('/images/heroes/' . $controller_hero_data[$i]["short_name"] . '.png') }}" data-heroimg="{{ asset('/images/heroes/' . $controller_hero_data[$i]["short_name"] . '.png') }}">
     </a>
   </div>
  @endfor
@endif
