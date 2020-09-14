
@if(count($controller_hero_data) > 0)
  @for($i = 0; $i < count($controller_hero_data); $i++)
    <div class="popup-trigger" data-herorolename="{{ $controller_hero_data[$i]["new_role"] }}" data-heroname="{{ $controller_hero_data[$i]["name"] }}" data-heroid="{{ $controller_hero_data[$i]["id"] }}" data-heroimg="{{ asset('/images/heroes/' . $controller_hero_data[$i]["short_name"] . '.png') }}">
      <div class="hero-picture ">
        <a href="javascript:void(0)" data-heroname="{{ $controller_hero_data[$i]["name"] }}">  <img src="{{ asset('/images/heroes/' . $controller_hero_data[$i]["short_name"] . '.png') }}" data-heroimg="{{ asset('/images/heroes/' . $controller_hero_data[$i]["short_name"] . '.png') }}"></a>
      </div>
      <div class="popup"><h4>{{ $controller_hero_data[$i]["name"] }}</h4>
        <h4>Value: {{ $controller_hero_data[$i]["value"] }}</h4>
        <a class="mobile-button" href="">Details</a>
      </div>
    </div>
  @endfor
@endif
