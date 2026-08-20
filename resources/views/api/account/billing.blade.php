@extends('layouts.api')

@section('title', 'Billing')

@section('content')
  <api-billing
    :stripekey="{{ json_encode($stripeKey) }}"
    :plans="{{ json_encode($plans) }}"
    :granted="{{ json_encode($granted) }}"
    :subscription="{{ json_encode($subscription) }}"
    :paymentmethod="{{ json_encode($paymentMethod) }}"
    :usage="{{ json_encode($usage) }}"
  >
  </api-billing>
@endsection

@push('scripts')
  <script src="https://js.stripe.com/v3/"></script>
@endpush
