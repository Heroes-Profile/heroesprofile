@extends('layouts.app')
@section('title', $title)

@section('content')
	<a class="btn btn-primary btn-sm ml-3" href="/Gamedata/Heroes?patch={{ $patch}}&locale={{ $locale }}">Back to Heroes</a>

	@include('Gamedata.form')

	<div class="bg-wrapper bg-light m-3 p-3">
		<img class="float-left mr-3 mb-3" src="/images/heroesimages/heroportraits/{{ $hero->portraits->target }}" alt="Hero Icon">

		<h2>{{ $hero->string('name') }} <span class="badge badge-secondary">{{ $hero->id() }}</span></h2>
		<p class="small">{!! $hero->string('description')->withoutColor()->withoutNewline()->asHtml() !!}</p>

		<div class="row">
			<div class="col-md">
				<h3 class="my-3">Identity</h3>
				<dl class="row">

					@foreach ($strings as $key => $display)

					<dt class="col-3 col-lg-3">{{ $display }}</dt>
					<dd class="col-3 col-lg-9">{!! $hero->string($key) ? $hero->string($key)->asHtml() : '<em>n/a</em>' !!}</dd>

					@endforeach

					<dt class="col-3 col-lg-3">Franchise</dt>
					<dd class="col-3 col-lg-9">{!! $hero->franchise ?? '<em>n/a</em>' !!}</dd>

					<dt class="col-3 col-lg-3">Gender</dt>
					<dd class="col-3 col-lg-9">{!! $hero->neutral ?? '<em>n/a</em>' !!}</dd>

					<dt class="col-3 col-lg-3">Rarity</dt>
					<dd class="col-3 col-lg-9">{!! $hero->rarity ?? '<em>n/a</em>' !!}</dd>
				</dl>
			</div>

			<div class="col-md">
				@if (isset($hero->descriptors))
				<h3 class="my-3">Tags</h3>
				<div class="row">
					<div class="col">

						@foreach ($hero->descriptors as $tag)
						<span class="badge badge-secondary">{{ $tag }}</span>
						@endforeach

					</div>
				</div>
				@endif

				<blockquote class="blockquote bg-white p-3 my-3">{{ $hero->string('infotext') }}</blockquote>
			</div>

			<div class="col-md">
				<h3 class="my-3">Stats</h3>
				<dl class="row">
					<dt class="col-lg-3">Released</dt>
					<dd class="col-lg-9">{{ date('F j, Y', strtotime($hero->releaseDate)) }}</dd>

					@foreach ($stats as $key => $display)

					<dt class="col-lg-3">{{ $display }}</dt>
					<dd class="col-lg-9">{!! $hero->$key ?? '<em>n/a</em>' !!}</dd>

					@endforeach

					<dt class="col-lg-3">Life</dt>
					<dd class="col-lg-9">{{ $hero->life->amount }} (+{{ $hero->life->scale * 100 }}% per level)</dd>

					<dt class="col-lg-3">Regen</dt>
					<dd class="col-lg-9">{{ $hero->life->regenRate }} (+{{ $hero->life->regenScale * 100 }}% per level)</dd>

					<dt class="col-lg-3">Range</dt>
					<dd class="col-lg-9">{{ $hero->weapons[0]->range }}</dd>

					<dt class="col-lg-3">Damage</dt>
					<dd class="col-lg-9">{{ $hero->weapons[0]->damage }} (+{{ $hero->weapons[0]->damageScale * 100 }}% per level)</dd>

					<dt class="col-lg-3">Attacks</dt>
					<dd class="col-lg-9">{{ round(1 / $hero->weapons[0]->period, 1) }} per second</dd>
				</dl>
			</div>
		</div>

		<h3 class="my-3">Abilities</h3>
		<div id="abilities-wrapper" class="row flex-row flex-nowrap border-top border-bottom overflow-auto p-3">

			@foreach ($hero->abilities() as $ability)
			<div class="card text-dark bg-white mx-3" style="min-width: 300px;">
				<div class="card-header bg-info">
					<h5>{{ $ability->string('name') }}</h5>
				</div>
				<div class="card-body">
					<img class="float-left mr-3" src="/images/heroesimages/abilitytalents/{{ $ability->icon }}" alt="Ability Icon" style="max-height: 60px;">
					<p class="card-text small">{!! $ability->string('short') ? $ability->string('short')->asHtml() : '<em>n/a</em>' !!}</p>
				</div>
				<ul class="list-group list-group-flush">
					<li class="list-group-item">
						Type: {{ ucfirst($ability->type()) }}

						@if (ucfirst($ability->type()) !== $ability->abilityType)
						({{ $ability->abilityType }})
						@endif

					</li>
					<li class="list-group-item">{!! $ability->string('cooldown') ? $ability->string('cooldown')->asHtml() : 'Cooldown: <em>n/a</em>' !!}</li>

					@if ($hero->string('energytype'))
					<li class="list-group-item">{!! $ability->string('energy') ? $ability->string('energy')->withoutStandard()->asHtml() : $hero->string('energytype') . ': <em>n/a</em>' !!}</li>
					@endif
				</ul>

				<div class="card-body bg-secondary text-white">
					<p>{!! $ability->string('full') ? $ability->string('full')->withScaling()->asHtml() : '<em>n/a</em>' !!}</p>
				</div>
			</div>
			@endforeach

		</div>

		<h3 class="my-3">Talents</h3>
		<ul class="nav nav-pills nav-justified mb-3" id="talents" role="tablist">

			@foreach ([1, 4, 7, 10, 13, 16, 20] as $level)
			<li class="nav-item">
				<a class="nav-link {{ $level === 1 ? 'active' : '' }}" id="level{{ $level }}-tab" data-toggle="tab" href="#level{{ $level }}" role="tab" aria-controls="level{{ $level }}" aria-selected="{{ $level === 1 ? 'true' : 'false' }}">Level {{ $level }}</a>
			</li>
			@endforeach
		</ul>
		<div class="tab-content m-3" id="talentsContent">

			@foreach ([1, 4, 7, 10, 13, 16, 20] as $level)
			<div class="tab-pane fade {{ $level === 1 ? 'show active' : '' }}" id="level{{ $level }}" role="tabpanel" aria-labelledby="level{{ $level }}-tab">
				@foreach ($hero->talents($level) as $talent)
				<div class="row py-2 border-top bg-secondary text-white">
					<div class="col-sm col-lg-4 col-xl-2">
						<img class="float-left mr-3" src="/images/heroesimages/abilitytalents/{{ $talent->icon }}" alt="Talent Icon" style="max-height: 80px;">
						<strong>{{ $talent->string('name') }}</strong>
						<p class="small">{!! $talent->string('cooldown') ? $talent->string('cooldown')->asHtml() : '' !!}</p>
					</div>
					<div class="col-lg">
						{!! $talent->string('full')->withScaling()->asHtml() !!}
					</div>
				</div>
				@endforeach
			</div>
			@endforeach
		</div>

		<div class="row my-5">
			<div class="col text-center">
				<img class="img-fluid" src="/images/heroesimages/heroportraits/{{ $hero->portraits->draftScreen }}" alt="Hero Portrait">
			</div>
		</div>
	</div>

<style>
#abilities-wrapper {
	overflow-x: scroll !important;
}
#abilities-wrapper::-webkit-scrollbar {
    -webkit-appearance: none;
    width: 8px;
}
#abilities-wrapper::-webkit-scrollbar-track {
    border-radius: 8px;
    background-color: rgba(156, 156, 156, .6);
}
#abilities-wrapper::-webkit-scrollbar-thumb {
    border-radius: 8px;
    background-color: rgba(57,57,57, .6);
}
</style>

@endsection
