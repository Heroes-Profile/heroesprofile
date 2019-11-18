@extends ('layouts.app')

@section ('searchform')

@endsection

@section('datatable')
 <search-form :rawfields='@json($rawfields)' :primaryfields='@json($primaryfields)' :secondaryfields='@json($secondaryfields)'></search-form>
  <data-table :dataurl="'{{ $dataurl }}'"></data-table>
@endsection
