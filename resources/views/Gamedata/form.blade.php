
	<form name="gamedata-form" method="GET">
		@csrf
		<div class="row">
			<div class="col">
				<select name="patch" class="custom-select">
					<option value="">Patch...</option>
					@foreach ($patches as $p)
					<option {{ $p === $patch ? 'selected' : '' }}>{{ $p }}</option>
					@endforeach
				</select>
			</div>

			<div class="col">
				<select name="locale" class="custom-select">
					<option value="">Locale...</option>
					@foreach ($locales as $country => $l)
					<option value="{{ $l }}" {{ $l === $locale ? 'selected' : '' }}>{{ ucfirst(strtolower($country)) }}</option>
					@endforeach
				</select>
			</div>

			<div class="col">
				<button class="btn btn-primary" type="submit">Submit</button>
			</div>
		</div>
	</form>
