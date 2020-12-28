@extends('layouts.app')
@section('title', $title)

@section('content')
	@include('Gamedata.form')

	<table class="table table-striped">
		<thead>
			<tr>
				<th scope="col">Name</th>
				<th scope="col">Title</th>
				<th scope="col">Type</th>
				<th scope="col">Role</th>
				<th scope="col">Difficulty</th>
				<th scope="col">Description</th>
				<th scope="col">Info</th>
			</tr>
		</thead>
		<tbody>

			@foreach ($heroes as $hero)
			<tr>
				<td>{{ $hero->string('name') }}</td>
				<td>{{ $hero->string('title') }}</td>
				<td>{{ $hero->string('type') }}</td>
				<td>{{ $hero->string('expandedrole') }}</td>
				<td>{{ $hero->string('difficulty') }}</td>
				<td>{{ $hero->string('description') }}</td>
				<td>{{ $hero->string('infotext') }}</td>
			</tr>
			@endforeach

		</tbody>
	</table>

@endsection
