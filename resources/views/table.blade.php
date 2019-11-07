@extends ('layouts.app')

@section ('searchform')

@endsection

@section('datatable')
  <h3 class="table-heading">{{$tableheading}}</h3>
  <search-form :rawfields='@json($rawfields)'></search-form>
  <data-table :dataurl="'{{ $dataurl }}'"></data-table>
@endsection
