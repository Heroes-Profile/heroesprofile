@extends('layouts.app')
@section('title', $title)

@section('content')

	<form name="gamedata" action="/Gamedata" method="POST">
		@csrf
		<div class="row">
			<div class="col">
				<select name="patch" class="custom-select">
					<option value="">Patch...</option>
					@foreach ($patches as $patch)
					<option>{{ $patch }}</option>
					@endforeach
				</select>
			</div>

			<div class="col">
				<select name="locale" class="custom-select">
					<option value="">Locale...</option>
					@foreach ($locales as $country => $locale)
					<option value="{{ $locale }}">{{ ucfirst(strtolower($country)) }}</option>
					@endforeach
				</select>
			</div>

			<div class="col">
				<select name="entity" class="custom-select">
					<option value="">Type...</option>
					<option value="hero">Hero</option>
				</select>
			</div>

			<div class="col">
				<button class="btn btn-primary" type="submit">Submit</button>
			</div>
		</div>
	</form>

@endsection
