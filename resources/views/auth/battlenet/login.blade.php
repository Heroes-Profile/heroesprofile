@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Battlenet Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="/authenticate/battlenet">
                        @csrf
                        <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <label class="input-group-text" for="input-region-select">Region</label>
                          </div>
                          <select class="custom-select" id="input-region-select" name="region" required="true" oninvalid="this.setCustomValidity('Please select a region from the list')" oninput="setCustomValidity('')">
                            <option disabled selected value>Choose...</option>
                            <option value="us">NA</option>
                            <option value="eu">EU</option>
                            <option value="kr">KR</option>
                            <option value="cn">CN</option>
                          </select>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Authenticate to Battlenet') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@endsection
