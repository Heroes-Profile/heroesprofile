@extends('layouts.app')
@section('title', $title)

@section('content')
	@include('Gamedata.form')

	<div class="wrapper bg-light m-3 p-3">
		<div class="float-right">{{ $hero->portraits->draftScreen }}</div>
		<h2>{{ $hero->string('name') }} <span class="badge badge-secondary">{{ $hero->id() }}</span></h2>
		<p class="small">{{ $hero->string('description') }}</p>

		<div class="row">
			<div class="col-md">
				<h3>Identity</h3>
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
				<blockquote class="blockquote bg-white p-3">{{ $hero->string('infotext') }}</blockquote>
			</div>
			<div class="col-md">
				<h3>Stats</h3>
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

		@if (isset($hero->descriptors))

		<h3>Tags</h3>
		<div class="row">
			<div class="col">

				@foreach ($hero->descriptors as $tag)
				<span class="badge badge-secondary">{{ $tag }}</span>
				@endforeach

			</div>
		</div>
		@endif			

	</div>

@endsection
